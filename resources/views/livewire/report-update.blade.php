<div>
    <x-navbar />
    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">
            <div class="content-wrapper">

                <!-- Title -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h4 class="font-weight-bold mb-1" style="color:#2d3748;">
                                    <i class="mdi mdi-clipboard-list-outline mr-2" style="color:#4e73df;"></i>Pending Transactions
                                </h4>
                                <p class="text-muted mb-0 small">
                                    <i class="mdi mdi-information-outline mr-1"></i>
                                    Records without an account number that have not yet been completed
                                </p>
                            </div>
                            <div class="d-flex align-items-center mt-2 mt-md-0" style="gap:8px;">
                                <span class="badge px-3 py-2" style="background:#e8f0fe; color:#3d5a99; font-size:0.8rem; border-radius:20px;">
                                    <i class="mdi mdi-database mr-1"></i>{{ number_format($counts->total) }} total records
                                </span>
                                <span class="badge px-3 py-2" style="background:#f0f3ff; color:#858796; font-size:0.78rem; border-radius:20px;">
                                    <i class="mdi mdi-clock-outline mr-1"></i>{{ now()->format('d M Y, H:i') }}
                                </span>
                                <button wire:click="exportFiltered"
                                    class="btn btn-sm font-weight-bold d-flex align-items-center"
                                    style="background:#217346; color:#fff; border-radius:20px; padding:5px 16px; gap:5px;">
                                    <i class="mdi mdi-microsoft-excel" style="font-size:1rem;"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <!-- With Customer -->
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm h-100 ru-card" style="border-left:4px solid #6c757d !important;">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-uppercase font-weight-bold mb-1" style="font-size:0.65rem; color:#6c757d; letter-spacing:0.05em;">With Customer</div>
                                        <h3 class="font-weight-bold mb-0" style="color:#495057;">{{ number_format($counts->with_customer) }}</h3>
                                    </div>
                                    <div class="ru-icon-wrap" style="background:#f0f0f0;">
                                        <i class="mdi mdi-account-outline" style="color:#6c757d; font-size:1.4rem;"></i>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="mdi mdi-circle-small"></i>No lecan link attached
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- With DTM -->
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm h-100 ru-card" style="border-left:4px solid #f0ad4e !important;">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-uppercase font-weight-bold mb-1" style="font-size:0.65rem; color:#856404; letter-spacing:0.05em;">With DTM</div>
                                        <h3 class="font-weight-bold mb-0" style="color:#856404;">{{ number_format($counts->with_dtm) }}</h3>
                                    </div>
                                    <div class="ru-icon-wrap" style="background:#fff8e1;">
                                        <i class="mdi mdi-account-hard-hat" style="color:#f0ad4e; font-size:1.4rem;"></i>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="mdi mdi-circle-small"></i>Lecan link present
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Regional Billing -->
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm h-100 ru-card" style="border-left:4px solid #17a2b8 !important;">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-uppercase font-weight-bold mb-1" style="font-size:0.65rem; color:#0c5460; letter-spacing:0.05em;">Regional Billing</div>
                                        <h3 class="font-weight-bold mb-0" style="color:#0c5460;">{{ number_format($counts->regional_billing) }}</h3>
                                    </div>
                                    <div class="ru-icon-wrap" style="background:#e0f7fa;">
                                        <i class="mdi mdi-file-document-outline" style="color:#17a2b8; font-size:1.4rem;"></i>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="mdi mdi-circle-small"></i>Status 2, awaiting evaluation
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- With Billing -->
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm h-100 ru-card" style="border-left:4px solid #4e73df !important;">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-uppercase font-weight-bold mb-1" style="font-size:0.65rem; color:#224abe; letter-spacing:0.05em;">With Billing</div>
                                        <h3 class="font-weight-bold mb-0" style="color:#224abe;">{{ number_format($counts->with_billing) }}</h3>
                                    </div>
                                    <div class="ru-icon-wrap" style="background:#e8f0fe;">
                                        <i class="mdi mdi-cash-register" style="color:#4e73df; font-size:1.4rem;"></i>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="mdi mdi-circle-small"></i>Status 2, evaluated = yes
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- With Regional Head -->
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm h-100 ru-card" style="border-left:4px solid #28a745 !important;">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-uppercase font-weight-bold mb-1" style="font-size:0.65rem; color:#155724; letter-spacing:0.05em;">Regional Head</div>
                                        <h3 class="font-weight-bold mb-0" style="color:#155724;">{{ number_format($counts->regional_head) }}</h3>
                                    </div>
                                    <div class="ru-icon-wrap" style="background:#d4edda;">
                                        <i class="mdi mdi-account-tie" style="color:#28a745; font-size:1.4rem;"></i>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="mdi mdi-circle-small"></i>Status 2, evaluated = approved
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Ready for Account -->
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm h-100 ru-card" style="border-left:4px solid #6f42c1 !important;">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-uppercase font-weight-bold mb-1" style="font-size:0.65rem; color:#4b2d8a; letter-spacing:0.05em;">Ready for Account</div>
                                        <h3 class="font-weight-bold mb-0" style="color:#4b2d8a;">{{ number_format($counts->ready_for_account) }}</h3>
                                    </div>
                                    <div class="ru-icon-wrap" style="background:#ede7f6;">
                                        <i class="mdi mdi-check-decagram" style="color:#6f42c1; font-size:1.4rem;"></i>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="mdi mdi-circle-small"></i>Status 2, map_id set, meter paid
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected -->
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <div class="card border-0 shadow-sm h-100 ru-card" style="border-left:4px solid #dc3545 !important;">
                            <div class="card-body py-3 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="text-uppercase font-weight-bold mb-1" style="font-size:0.65rem; color:#721c24; letter-spacing:0.05em;">Rejected</div>
                                        <h3 class="font-weight-bold mb-0" style="color:#721c24;">{{ number_format($counts->rejected) }}</h3>
                                    </div>
                                    <div class="ru-icon-wrap" style="background:#f8d7da;">
                                        <i class="mdi mdi-close-circle-outline" style="color:#dc3545; font-size:1.4rem;"></i>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="mdi mdi-circle-small"></i>Status 5
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2 d-flex align-items-center">
                        <i class="mdi mdi-filter-outline mr-2 text-primary"></i>
                        <span class="font-weight-bold small text-primary">Search & Filter</span>
                    </div>
                    <div class="card-body py-3">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="mb-1 font-weight-bold small">
                                    <i class="mdi mdi-magnify mr-1 text-muted"></i>Search
                                </label>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model.live.debounce.400ms="filterSearch"
                                    placeholder="Map ID or Tracking ID...">
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="mb-1 font-weight-bold small">
                                    <i class="mdi mdi-calendar-start mr-1 text-muted"></i>Start Date
                                </label>
                                <input type="date" class="form-control form-control-sm"
                                    wire:model.live="filterDateFrom">
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="mb-1 font-weight-bold small">
                                    <i class="mdi mdi-calendar-end mr-1 text-muted"></i>End Date
                                </label>
                                <input type="date" class="form-control form-control-sm"
                                    wire:model.live="filterDateTo">
                            </div>
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label class="mb-1 font-weight-bold small d-block">&nbsp;</label>
                                <button wire:click="exportFiltered"
                                    class="btn btn-sm font-weight-bold w-100 d-flex align-items-center justify-content-center"
                                    style="background:#217346; color:#fff; border-radius:6px; gap:5px;">
                                    <i class="mdi mdi-microsoft-excel" style="font-size:1rem;"></i> Export Excel
                                </button>
                            </div>
                            <div class="col-md-2">
                                <label class="mb-1 font-weight-bold small d-block">&nbsp;</label>
                                <button class="btn btn-sm btn-outline-secondary w-100"
                                    wire:click="clearFilters">
                                    <i class="mdi mdi-close mr-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Records Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-2 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-table mr-2 text-primary"></i>
                            <span class="font-weight-bold small text-primary">Transaction Records</span>
                        </div>
                        <span class="badge badge-light" style="font-size:0.75rem;">
                            {{ number_format($records->total()) }} record(s)
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0" style="border-collapse:separate; border-spacing:0;">
                                @php
                                    $thBase = 'font-size:0.75rem; font-weight:600; border:none; white-space:nowrap;';
                                    $thSort = $thBase . ' cursor:pointer; user-select:none;';
                                    function sortIcon($col, $sortCol, $sortDir, $sortable) {
                                        $resolved = $sortable[$col] ?? null;
                                        if ($resolved === $sortCol) {
                                            return $sortDir === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down';
                                        }
                                        return 'mdi-arrow-up-down';
                                    }
                                @endphp
                                <thead style="background:linear-gradient(135deg,#343a40,#495057); color:#fff;">
                                    <tr>
                                        <th class="py-2 px-3" style="{{ $thBase }}">#</th>
                                        <th class="py-2 px-3 ru-th-sort" wire:click="sort('tracking_id')" style="{{ $thSort }}">
                                            <i class="mdi mdi-identifier mr-1" style="opacity:0.7;"></i>Tracking ID
                                            <i class="mdi {{ sortIcon('tracking_id', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                        <th class="py-2 px-3 ru-th-sort" wire:click="sort('firstname')" style="{{ $thSort }}">
                                            <i class="mdi mdi-account mr-1" style="opacity:0.7;"></i>Customer Name
                                            <i class="mdi {{ sortIcon('firstname', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                        <th class="py-2 px-3 ru-th-sort" wire:click="sort('map_id')" style="{{ $thSort }}">
                                            <i class="mdi mdi-map-marker-outline mr-1" style="opacity:0.7;"></i>MAP ID
                                            <i class="mdi {{ sortIcon('map_id', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                        <th class="py-2 px-3 ru-th-sort" wire:click="sort('region')" style="{{ $thSort }}">
                                            <i class="mdi mdi-map-marker mr-1" style="opacity:0.7;"></i>Region
                                            <i class="mdi {{ sortIcon('region', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                        <th class="py-2 px-3 ru-th-sort" wire:click="sort('business_hub')" style="{{ $thSort }}">
                                            <i class="mdi mdi-office-building mr-1" style="opacity:0.7;"></i>Business Hub
                                            <i class="mdi {{ sortIcon('business_hub', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                        <th class="py-2 px-3 ru-th-sort" wire:click="sort('service_center')" style="{{ $thSort }}">
                                            <i class="mdi mdi-store mr-1" style="opacity:0.7;"></i>Service Center
                                            <i class="mdi {{ sortIcon('service_center', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                        <th class="py-2 px-3" style="{{ $thBase }}">
                                            <i class="mdi mdi-home-outline mr-1" style="opacity:0.7;"></i>Address
                                        </th>
                                        <th class="text-center py-2 px-3 ru-th-sort" wire:click="sort('status')" style="{{ $thSort }}">
                                            <i class="mdi mdi-tag mr-1" style="opacity:0.7;"></i>Status
                                            <i class="mdi {{ sortIcon('status', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                        <th class="py-2 px-3 ru-th-sort" wire:click="sort('dss')" style="{{ $thSort }}">
                                            <i class="mdi mdi-link-variant mr-1" style="opacity:0.7;"></i>DSS
                                            <i class="mdi {{ sortIcon('dss', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                        <th class="py-2 px-3" style="{{ $thBase }}">
                                            <i class="mdi mdi-comment-text-outline mr-1" style="opacity:0.7;"></i>Remarks
                                        </th>
                                        <th class="py-2 px-3 ru-th-sort" wire:click="sort('created_at')" style="{{ $thSort }}">
                                            <i class="mdi mdi-calendar mr-1" style="opacity:0.7;"></i>Date Added
                                            <i class="mdi {{ sortIcon('created_at', $sortCol, $sortDir, $sortable) }} ru-sort-icon"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($records as $rec)
                                        @php
                                            $s         = (int) $rec->status;
                                            $lecanNull = empty($rec->lecan_link);

                                            if (in_array($s, [0, 1]) && $lecanNull) {
                                                $badge = ['label' => 'With Customer',    'icon' => 'mdi-account-outline',       'class' => 'ru-badge-customer'];
                                            } elseif (in_array($s, [0, 1]) && !$lecanNull) {
                                                $badge = ['label' => 'With DTM',         'icon' => 'mdi-account-hard-hat',      'class' => 'ru-badge-dtm'];
                                            } elseif ($s === 2 && (string)$rec->evaluated === 'no') {
                                                $badge = ['label' => 'Regional Billing', 'icon' => 'mdi-file-document-outline', 'class' => 'ru-badge-billing'];
                                            } elseif ($s === 2 && (string)$rec->evaluated === 'approved') {
                                                $badge = ['label' => 'Regional Head', 'icon' => 'mdi-account-tie',      'class' => 'ru-badge-reg-head'];
                                            } elseif ($s === 2 && (string)$rec->evaluated === 'yes') {
                                                $badge = ['label' => 'With Billing', 'icon' => 'mdi-cash-register',       'class' => 'ru-badge-with-billing'];
                                            } elseif ($s === 5) {
                                                $badge = ['label' => 'Rejected',     'icon' => 'mdi-close-circle-outline', 'class' => 'ru-badge-rejected'];
                                            } else {
                                                $badge = ['label' => 'Status ' . $s, 'icon' => 'mdi-help-circle-outline',  'class' => 'ru-badge-other'];
                                            }
                                        @endphp
                                        <tr class="ru-row">
                                            <td class="align-middle px-3 text-muted" style="font-size:0.78rem;">{{ $records->firstItem() + $loop->index }}</td>
                                            <td class="align-middle px-3">
                                                <a href="{{ route('account_details', $rec->tracking_id) }}" target="_blank"
                                                   class="ru-tracking-link">
                                                    <i class="mdi mdi-open-in-new mr-1" style="font-size:0.75rem;"></i>{{ $rec->tracking_id }}
                                                </a>
                                            </td>
                                            <td class="align-middle px-3" style="font-size:0.82rem;">
                                                @php $name = trim(($rec->firstname ?? '') . ' ' . ($rec->other_name ?? '')); @endphp
                                                @if($name)
                                                    <span class="d-flex align-items-center">
                                                        <span class="ru-avatar mr-2">{{ strtoupper(substr($rec->firstname ?? '?', 0, 1)) }}</span>
                                                        {{ $name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="align-middle px-3" style="font-size:0.82rem;">
                                                @if($rec->map_id)
                                                    <span class="text-monospace" style="font-size:0.78rem;">{{ $rec->map_id }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="align-middle px-3" style="font-size:0.82rem;">{{ $rec->region ?: '—' }}</td>
                                            <td class="align-middle px-3" style="font-size:0.82rem;">{{ $rec->business_hub ?: '—' }}</td>
                                            <td class="align-middle px-3" style="font-size:0.82rem;">{{ $rec->service_center ?: '—' }}</td>
                                            <td class="align-middle px-3" style="font-size:0.82rem; max-width:180px; word-break:break-word;">
                                                {{ trim(($rec->house_no ? $rec->house_no . ', ' : '') . ($rec->full_address ?: '')) ?: '—' }}
                                            </td>
                                            <td class="text-center align-middle px-3">
                                                <span class="ru-badge {{ $badge['class'] }}">
                                                    <i class="mdi {{ $badge['icon'] }} mr-1"></i>{{ $badge['label'] }}
                                                </span>
                                            </td>
                                            <td class="align-middle px-3" style="font-size:0.82rem;">{{ $rec->dss ?: '—' }}</td>
                                            <td class="align-middle px-3" style="max-width:200px;">
                                                @php
                                                    $hasDtm     = !empty($rec->dtm_comment);
                                                    $hasBilling = !empty($rec->billing_comment);
                                                    $hasComment = $s === 5 && !empty($rec->comment);
                                                @endphp
                                                @if($hasDtm || $hasBilling || $hasComment)
                                                    <div class="d-flex flex-column" style="gap:4px;">
                                                        @if($hasDtm)
                                                            <div class="ru-remark ru-remark-dtm">
                                                                <span class="ru-remark-label">DTM</span>
                                                                <span class="ru-remark-text">{{ $rec->dtm_comment }}</span>
                                                            </div>
                                                        @endif
                                                        @if($hasBilling)
                                                            <div class="ru-remark ru-remark-billing">
                                                                <span class="ru-remark-label">Billing</span>
                                                                <span class="ru-remark-text">{{ $rec->billing_comment }}</span>
                                                            </div>
                                                        @endif
                                                        @if($hasComment)
                                                            <div class="ru-remark ru-remark-rejected">
                                                                <span class="ru-remark-label">Reason</span>
                                                                <span class="ru-remark-text">{{ $rec->comment }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="align-middle px-3 text-nowrap" style="font-size:0.78rem; color:#6c757d;">
                                                <i class="mdi mdi-calendar-outline mr-1"></i>
                                                {{ $rec->created_at ? \Carbon\Carbon::parse($rec->created_at)->format('d M Y') : '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted py-5">
                                                <i class="mdi mdi-check-circle-outline text-success" style="font-size:3rem; display:block; margin-bottom:0.75rem;"></i>
                                                <strong>All clear!</strong><br>
                                                <small>No pending transactions found for the selected filters.</small>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-3 border-top d-flex justify-content-between align-items-center flex-wrap" style="background:#fafbff;">
                            <small class="text-muted mb-2 mb-md-0">
                                <i class="mdi mdi-format-list-numbered mr-1"></i>
                                Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ number_format($records->total()) }} records
                            </small>
                            <div>{{ $records->links() }}</div>
                        </div>
                    </div>
                </div>

            </div>
            <x-footer />
        </div>
    </div>

    <style>
        /* Cards */
        .ru-card { transition: transform 0.15s ease, box-shadow 0.15s ease; border-radius:8px !important; }
        .ru-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important; }
        .ru-icon-wrap { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

        /* Table rows */
        .ru-row { transition: background 0.1s ease; }
        .ru-row:hover td { background:#f0f4ff !important; }
        .ru-row td { border-color:#f0f0f0 !important; vertical-align:middle; }

        /* Tracking link */
        .ru-tracking-link { font-weight:600; font-size:0.82rem; color:#4e73df; text-decoration:none; }
        .ru-tracking-link:hover { color:#224abe; text-decoration:underline; }

        /* Avatar initials */
        .ru-avatar { width:26px; height:26px; border-radius:50%; background:#4e73df; color:#fff;
                     font-size:0.7rem; font-weight:700; display:inline-flex; align-items:center;
                     justify-content:center; flex-shrink:0; }

        /* Status badges */
        .ru-badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; white-space:nowrap; }
        .ru-badge-customer  { background:#e9ecef; color:#495057; }
        .ru-badge-dtm       { background:#fff3cd; color:#856404; }
        .ru-badge-billing   { background:#d1ecf1; color:#0c5460; }
        .ru-badge-reg-head    { background:#d4edda; color:#155724; }
        .ru-badge-with-billing{ background:#e8f0fe; color:#224abe; }
        .ru-badge-rejected    { background:#f8d7da; color:#721c24; }

        /* Sortable headers */
        .ru-th-sort { cursor:pointer; transition: background 0.15s ease; }
        .ru-th-sort:hover { background:rgba(255,255,255,0.12) !important; }
        .ru-sort-icon { font-size:0.75rem; opacity:0.5; margin-left:3px; vertical-align:middle; transition:opacity 0.15s; }
        .ru-th-sort:hover .ru-sort-icon { opacity:1; }

        /* Remarks cell */
        .ru-remark { display:flex; align-items:flex-start; gap:5px; font-size:0.78rem; line-height:1.4; word-break:break-word; }
        .ru-remark-label { flex-shrink:0; font-weight:700; font-size:0.65rem; padding:1px 6px; border-radius:4px; margin-top:1px; letter-spacing:0.03em; text-transform:uppercase; }
        .ru-remark-text  { color:#495057; }
        .ru-remark-dtm     .ru-remark-label { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
        .ru-remark-billing .ru-remark-label { background:#e8f0fe; color:#224abe; border:1px solid #4e73df; }
        .ru-remark-rejected .ru-remark-label { background:#f8d7da; color:#721c24; border:1px solid #dc3545; }
        .ru-badge-other     { background:#f0f0f0; color:#6c757d; }

        /* Pagination */
        .pagination { margin-bottom:0; flex-wrap:wrap; }
        .pagination .page-item .page-link { color:#4e73df; border-color:#d1d3e2; padding:0.3rem 0.6rem; font-size:0.82rem; line-height:1.4; border-radius:6px !important; margin:0 2px; }
        .pagination .page-item.active .page-link { background-color:#4e73df; border-color:#4e73df; color:#fff; }
        .pagination .page-item.disabled .page-link { color:#b7b9cc; }
        .pagination .page-item .page-link:hover { background-color:#eaecf4; border-color:#d1d3e2; color:#224abe; }
    </style>
</div>
