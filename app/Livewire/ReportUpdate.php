<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\RoleEnum;

class ReportUpdate extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $filterSearch   = '';
    public string $filterDateFrom = '';
    public string $filterDateTo   = '';

    private string $userRegion = '';
    private bool   $isHQ       = false;

    private array $sortable = [
        'tracking_id'    => 'upload_houses.tracking_id',
        'firstname'      => 'ac.firstname',
        'map_id'         => 'upload_houses.map_id',
        'region'         => 'upload_houses.region',
        'business_hub'   => 'upload_houses.business_hub',
        'service_center' => 'upload_houses.service_center',
        'status'         => 'upload_houses.status',
        'dss'            => 'upload_houses.dss',
        'paid_for_meter' => 'upload_houses.paid_for_meter',
        'created_at'     => 'upload_houses.created_at',
    ];

    public string $sortCol = 'upload_houses.created_at';
    public string $sortDir = 'desc';

    public function mount(): void
    {
        $user           = Auth::user();
        $this->isHQ     = $user->region === 'HQ' || $user->authority === RoleEnum::super_admin()->value;
        $this->userRegion = $this->isHQ ? '' : $user->region;
    }

    public function updatedFilterSearch()   { $this->resetPage(); }
    public function updatedFilterDateFrom() { $this->resetPage(); }
    public function updatedFilterDateTo()   { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterSearch   = '';
        $this->filterDateFrom = '';
        $this->filterDateTo   = '';
        $this->resetPage();
    }

    public function exportFiltered(): void
    {
        $this->redirect(route('report_update.export', [
            'search'    => $this->filterSearch,
            'date_from' => $this->filterDateFrom,
            'date_to'   => $this->filterDateTo,
            'type'      => 'pending',
        ]), navigate: false);
    }

    public function sort(string $col): void
    {
        if (!isset($this->sortable[$col])) return;

        $resolved = $this->sortable[$col];

        if ($this->sortCol === $resolved) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortCol = $resolved;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    private function baseQuery()
    {
        $query = DB::table('upload_houses')
            ->leftJoin('continue_account_creations as cac', 'upload_houses.customer_id', '=', 'cac.customer_id')
            ->leftJoin('account_creations as ac', 'cac.customer_id', '=', 'ac.id')
            ->where('upload_houses.status', '!=', 4)
            ->whereNull('upload_houses.account_no')
            ->whereNull('upload_houses.deleted_at')
            ->whereIn('upload_houses.paid_for_meter', ['Yes'])
            ->whereNotNull('upload_houses.map_id')
            ->whereNotNull('upload_houses.dss')
            ->whereNotNull('upload_houses.lecan_link');

        if (!empty($this->userRegion)) {
            $query->where('upload_houses.region', $this->userRegion);
        }

        if (!empty($this->filterSearch)) {
            $search = $this->filterSearch;
            $query->where(function ($q) use ($search) {
                $q->where('upload_houses.map_id', 'like', '%' . $search . '%')
                  ->orWhere('upload_houses.tracking_id', 'like', '%' . $search . '%');
            });
        }

        if (!empty($this->filterDateFrom)) {
            $query->where('upload_houses.created_at', '>=', $this->filterDateFrom . ' 00:00:00');
        }

        if (!empty($this->filterDateTo)) {
            $query->where('upload_houses.created_at', '<=', $this->filterDateTo . ' 23:59:59');
        }

        return $query->select([
            'upload_houses.id', 'upload_houses.tracking_id', 'upload_houses.customer_id',
            'upload_houses.house_no', 'upload_houses.service_center', 'upload_houses.full_address',
            'upload_houses.region', 'upload_houses.business_hub', 'upload_houses.status',
            'upload_houses.map_id', 'upload_houses.dss', 'upload_houses.lecan_link',
            'upload_houses.paid_for_meter',
            'upload_houses.evaluated', 'upload_houses.comment',
            'upload_houses.dtm_comment', 'upload_houses.billing_comment',
            'upload_houses.created_at',
            'ac.firstname', 'ac.other_name',
        ])->orderBy($this->sortCol, $this->sortDir);
    }

    private function counts()
    {
        $q = DB::table('upload_houses')
            ->where('upload_houses.status', '!=', 4)
            ->whereNull('upload_houses.account_no')
            ->whereNull('upload_houses.deleted_at');

        if (!empty($this->userRegion)) {
            $q->where('upload_houses.region', $this->userRegion);
        }

        if (!empty($this->filterSearch)) {
            $search = $this->filterSearch;
            $q->where(function ($qq) use ($search) {
                $qq->where('upload_houses.map_id', 'like', '%' . $search . '%')
                   ->orWhere('upload_houses.tracking_id', 'like', '%' . $search . '%');
            });
        }

        if (!empty($this->filterDateFrom)) {
            $q->where('upload_houses.created_at', '>=', $this->filterDateFrom . ' 00:00:00');
        }

        if (!empty($this->filterDateTo)) {
            $q->where('upload_houses.created_at', '<=', $this->filterDateTo . ' 23:59:59');
        }

        return $q->selectRaw("
            COUNT(*) AS total,
            COUNT(CASE WHEN upload_houses.status IN (0,1) AND (upload_houses.lecan_link IS NULL OR upload_houses.lecan_link = '') THEN 1 END) AS with_customer,
            COUNT(CASE WHEN upload_houses.status IN (0,1) AND upload_houses.lecan_link IS NOT NULL AND upload_houses.lecan_link != '' THEN 1 END) AS with_dtm,
            COUNT(CASE WHEN upload_houses.status = 2 AND upload_houses.evaluated = 'no' THEN 1 END) AS regional_billing,
            COUNT(CASE WHEN upload_houses.status = 2 AND upload_houses.evaluated = 'approved' THEN 1 END) AS regional_head,
            COUNT(CASE WHEN upload_houses.status = 2 AND upload_houses.evaluated = 'yes' THEN 1 END) AS with_billing,
            COUNT(CASE WHEN upload_houses.status = 5 THEN 1 END) AS rejected,
            COUNT(CASE WHEN upload_houses.status = 2
                        AND upload_houses.evaluated = 'yes'
                        AND upload_houses.map_id IS NOT NULL
                        AND upload_houses.map_id != ''
                        AND upload_houses.account_no IS NULL
                        AND upload_houses.paid_for_meter IN ('Yes', 'Old')
                   THEN 1 END) AS ready_for_account
        ")->first();
    }

    public function render()
    {
        return view('livewire.report-update', [
            'records'  => $this->baseQuery()->paginate(50),
            'counts'   => $this->counts(),
            'sortCol'  => $this->sortCol,
            'sortDir'  => $this->sortDir,
            'sortable' => $this->sortable,
        ]);
    }
}
