<div wire:poll.10s>
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">
            <div class="content-wrapper">

                {{-- Page Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="font-weight-bold mb-1">Account Details</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0 bg-transparent">
                                <li class="breadcrumb-item"><a href="/new_account" wire:navigation>New Accounts</a></li>
                                <li class="breadcrumb-item active text-primary">{{ $details->tracking_id }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $statusColors = [
                                'started'      => 'secondary',
                                'with-dtm'     => 'info',
                                'with-bhm'     => 'warning',
                                'with-billing' => 'primary',
                                'completed'    => 'success',
                                'rejected'     => 'danger',
                            ];
                            $color = $statusColors[$details->status] ?? 'dark';
                        @endphp
                        <span class="badge badge-{{ $color }} px-3 py-2" style="font-size:.85rem;">
                            {{ ucwords(str_replace('-', ' ', $details->status)) }}
                        </span>
                        <a href="/new_account" class="btn btn-outline-secondary btn-sm" wire:navigation>
                            <i class="mdi mdi-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>

                {{-- Flash Messages --}}
                @if(session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="mdi mdi-alert-circle-outline mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="mdi mdi-check-circle-outline mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if(session()->has('warning'))
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <i class="mdi mdi-alert-outline mr-2"></i>{{ session('warning') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                <div class="row">

                    {{-- ── LEFT COLUMN ── --}}
                    <div class="col-xl-8 col-lg-7">

                        {{-- Basic Information --}}
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-account-circle text-primary mr-2" style="font-size:1.25rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Basic Information</h6>
                            </div>
                            <div class="card-body pt-4">
                                <div class="row">
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Date Submitted</small>
                                        <span class="font-weight-medium">{{ $details->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Tracking ID</small>
                                        <span class="font-weight-medium text-primary">{{ $details->tracking_id }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Title</small>
                                        <span class="font-weight-medium">{{ $details->title ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Surname</small>
                                        <span class="font-weight-medium">{{ $details->surname }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">First Name</small>
                                        <span class="font-weight-medium">{{ $details->firstname }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Other Names</small>
                                        <span class="font-weight-medium">{{ $details->other_name ?: '—' }}</span>
                                    </div>
                                    @if(auth()->check() && auth()->user()->authority !== 'dtm')
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Email</small>
                                        <span class="font-weight-medium">{{ $details->email ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Phone</small>
                                        <span class="font-weight-medium">{{ $details->phone ?: '—' }}</span>
                                    </div>
                                    @endif
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Accounts Applied For</small>
                                        <span class="badge badge-primary px-2 py-1">{{ $details->uploadedPictures->count() }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Progress</small>
                                        <span class="badge badge-{{ $color }} px-2 py-1">{{ $details->status_name ?: ucwords(str_replace('-', ' ', $details->status)) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Landlord Information --}}
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-home-account text-success mr-2" style="font-size:1.25rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Landlord Information</h6>
                            </div>
                            <div class="card-body pt-4">
                                <div class="row">
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">NIN</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->nin_number ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Surname</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->landlord_surname ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Other Names</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->landlord_othernames ?: '—' }}</span>
                                    </div>
                                    @if(auth()->check() && auth()->user()->authority !== 'dtm')
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Telephone</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->landlord_telephone ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Email</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->landlord_email ?: '—' }}</span>
                                    </div>
                                    @endif
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Previous Employer</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->name_address_of_previous_employer ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Previous Account No.</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->previous_account_number ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Previous Meter No.</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->previous_meter_number ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Preferred Bill Method</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->prefered_method_recieving_bill ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">CAC Number</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->cac_number ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Organisation Name</small>
                                        <span class="font-weight-medium">{{ $details->continuation?->organisational_name ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Record ID</small>
                                        <span class="badge badge-light border">{{ $details->id }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Accounts Table --}}
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center justify-content-between bg-white border-bottom py-3">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-format-list-bulleted text-warning mr-2" style="font-size:1.25rem;"></i>
                                    <h6 class="mb-0 font-weight-semibold">Uploaded Accounts
                                        <span class="badge badge-warning ml-2">{{ $details->uploadedPictures->count() }}</span>
                                    </h6>
                                </div>

                                @canany(['super_admin', 'billing'])
                                    @if(in_array(auth()->user()->email, [
                                        'victor.ogiogio@ibedc.com','grace.odejayi@ibedc.com',
                                        'emmanuel.adeoye@ibedc.com','azeez.aderibigbe@ibedc.com',
                                        'janet.dairo@ibedc.com','basirat.akande@ibedc.com'
                                    ]))
                                    <button
                                        class="btn btn-sm btn-danger"
                                        wire:click="GenerateAll('{{ $details->tracking_id }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="GenerateAll"
                                    >
                                        <span wire:loading.remove wire:target="GenerateAll">
                                            <i class="mdi mdi-lightning-bolt mr-1"></i>Generate All
                                        </span>
                                        <span wire:loading wire:target="GenerateAll">
                                            <i class="mdi mdi-loading mdi-spin mr-1"></i>Processing...
                                        </span>
                                    </button>
                                    @endif
                                @endcanany
                            </div>

                            <div class="card-body p-0">
                                @if($details->uploadedPictures && $details->uploadedPictures->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" style="font-size:.85rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:36px;">
                                                    @if(in_array(auth()->user()->authority, ['super_admin','billing','region']))
                                                        <input type="checkbox" wire:model="selectAll">
                                                    @endif
                                                </th>
                                                <th>ID</th>
                                                <th>Region</th>
                                                <th>No.</th>
                                                <th>Address</th>
                                                <th>Hub</th>
                                                <th>Acct No.</th>
                                                <th>Evaluated</th>
                                                <th>Meter</th>
                                                <th>Map ID</th>
                                                <th>Status</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($details->uploadedPictures as $account)
                                            <tr>
                                                <td>
                                                    @if($account->status == 2 && $account->evaluated == "no" && auth()->user()->authority === 'billing')
                                                        <input type="checkbox" wire:model="selected" value="{{ $account->id }}">
                                                    @elseif($account->status == 2 && $account->evaluated == "approved" && auth()->user()->authority === 'region')
                                                        <input type="checkbox" wire:model="selected" value="{{ $account->id }}">
                                                    @endif
                                                </td>
                                                <td class="text-muted">{{ $account->id }}</td>
                                                <td>{{ $account->region }}</td>
                                                <td>{{ $account->house_no }}</td>
                                                <td style="max-width:160px;" class="text-truncate" title="{{ $account->full_address }}">
                                                    {{ $account->full_address }}
                                                </td>
                                                <td>{{ $account->business_hub }}</td>
                                                <td>
                                                    @if($account->account_no)
                                                        <code class="text-success">{{ $account->account_no }}</code>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($account->evaluated == "approved")
                                                        <span class="badge badge-success">Approved</span>
                                                    @elseif($account->evaluated == "yes")
                                                        <span class="badge badge-info">Yes</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ $account->evaluated }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(in_array($account->paid_for_meter, ['Yes','Old','Paid']))
                                                        <span class="badge badge-success">{{ $account->paid_for_meter }}</span>
                                                    @else
                                                        <span class="badge badge-light border">{{ $account->paid_for_meter ?: '—' }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted">{{ $account->map_id ?: '—' }}</td>
                                                <td>
                                                    @php
                                                        $s = $account->status;
                                                        $ev = $account->evaluated;
                                                    @endphp
                                                    @if($s == "0")
                                                        <span class="badge badge-secondary">Started</span>
                                                    @elseif($s == "1")
                                                        <span class="badge badge-info">With DTM</span>
                                                    @elseif($s == "3")
                                                        <span class="badge badge-warning text-dark">Compliance</span>
                                                    @elseif($s == "2" && $ev == "no")
                                                        <span class="badge badge-warning text-dark">Reg. Billing</span>
                                                    @elseif($s == "2" && $ev == "approved")
                                                        <span class="badge badge-warning text-dark">Reg. Head</span>
                                                    @elseif($s == "2" && $ev == "yes")
                                                        <span class="badge badge-primary">With Billing</span>
                                                    @elseif($s == "2")
                                                        <span class="badge badge-primary">With Billing</span>
                                                    @elseif($s == "4")
                                                        <span class="badge badge-success">Completed</span>
                                                    @elseif($s == "5")
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @elseif($s == "6")
                                                        <span class="badge badge-primary">Staged</span>
                                                    @else
                                                        <span class="badge badge-light border">N/A</span>
                                                    @endif
                                                </td>

                                                {{-- Actions column --}}
                                                <td>
                                                    <div class="d-flex flex-wrap align-items-center" style="gap:4px;">

                                                        {{-- View --}}
                                                        <a target="_blank"
                                                           href="/tracking_details/{{ $account->id }}/{{ $account->tracking_id }}"
                                                           class="btn btn-xs btn-outline-secondary"
                                                           title="View Details">
                                                            <i class="mdi mdi-eye"></i>
                                                        </a>

                                                        @canany(['rico','super_admin'])
                                                            @if($account->status == 3)
                                                            <button wire:click="ricoApprove({{ $details->id }}, {{ $account->id }})"
                                                                    class="btn btn-xs btn-success" title="Rico Approve">
                                                                Approve
                                                            </button>
                                                            @endif
                                                        @endcanany

                                                        @canany(['super_admin'])
                                                            @if($account->status == 5 && $account->picture != "" && $account->lecan_link != "")
                                                            <button wire:click="move({{ $account->id }})"
                                                                    class="btn btn-xs btn-info text-white" title="Move to DTM">
                                                                Move
                                                            </button>
                                                            @endif
                                                        @endcanany

                                                        @canany(['region'])
                                                            @if($account->status == 2 && $account->picture != "" && $account->lecan_link != "" && $account->evaluated == "approved")
                                                            <button wire:click="confirmBillingReject({{ $details->id }}, {{ $account->id }})"
                                                                    class="btn btn-xs btn-danger" title="Reject">
                                                                Reject
                                                            </button>
                                                            @endif
                                                        @endcanany

                                                        @canany(['billing','super_admin'])
                                                            @if($account->account_no && $account->status == 2)
                                                            <button wire:click="generate({{ $details->id }}, {{ $account->id }})"
                                                                    class="btn btn-xs btn-primary">
                                                                Approve
                                                            </button>
                                                            @endif

                                                            @if(!$account->account_no && $account->status == 2)
                                                                @if(auth()->check() && auth()->user()->default_password == '1')
                                                                    @if($account->evaluated == 'yes' && $account->status == 2)
                                                                        <button wire:click="confirmBillingReject({{ $details->id }}, {{ $account->id }})"
                                                                                class="btn btn-xs btn-danger">
                                                                            Reject
                                                                        </button>

                                                                        @if(in_array($account->paid_for_meter, ['Yes','Old']) &&
                                                                            in_array(auth()->user()->email, ['victor.ogiogio@ibedc.com','tolulope.olaniyan@ibedc.com']))
                                                                        <button wire:click="stageUBVSAccount({{ $details->id }}, {{ $account->id }})"
                                                                                wire:loading.attr="disabled"
                                                                                class="btn btn-xs btn-secondary"
                                                                                title="Generate (Pass)">
                                                                            <span wire:loading.remove wire:target="stageUBVSAccount({{ $details->id }}, {{ $account->id }})">Gen(Pass)</span>
                                                                            <span wire:loading wire:target="stageUBVSAccount({{ $details->id }}, {{ $account->id }})"><i class="mdi mdi-loading mdi-spin"></i></span>
                                                                        </button>
                                                                        @endif

                                                                        @if(in_array($account->paid_for_meter, ['Yes','Paid']))
                                                                        <button wire:click="stageUBVSAccount({{ $details->id }}, {{ $account->id }})"
                                                                                wire:loading.attr="disabled"
                                                                                class="btn btn-xs btn-success"
                                                                                title="Generate via UBVS">
                                                                            <span wire:loading.remove wire:target="stageUBVSAccount({{ $details->id }}, {{ $account->id }})">UBVS</span>
                                                                            <span wire:loading wire:target="stageUBVSAccount({{ $details->id }}, {{ $account->id }})"><i class="mdi mdi-loading mdi-spin"></i></span>
                                                                        </button>
                                                                        @endif

                                                                        <button wire:click="closeAccount({{ $details->id }}, {{ $account->id }})"
                                                                                class="btn btn-xs btn-dark" title="Close Account">
                                                                            Close
                                                                        </button>
                                                                    @endif
                                                                @endif

                                                                @if(auth()->check() && auth()->user()->default_password == '9' && $account->evaluated == 'no')
                                                                <button wire:click="approveaccountbilling({{ $details->id }}, {{ $account->id }})"
                                                                        class="btn btn-xs btn-primary">
                                                                    Approve
                                                                </button>
                                                                <button wire:click="confirmBillingReject({{ $details->id }}, {{ $account->id }})"
                                                                        class="btn btn-xs btn-danger">
                                                                    Reject
                                                                </button>
                                                                @endif
                                                            @endif
                                                        @endcanany

                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Approve Selected --}}
                                @canany(['region'])
                                    @if(count($selected) > 0)
                                    <div class="p-3 border-top">
                                        <button wire:click="approveSelected"
                                                class="btn btn-success btn-sm"
                                                wire:loading.attr="disabled">
                                            <i class="mdi mdi-check-all mr-1"></i>
                                            Approve Selected ({{ count($selected) }})
                                        </button>
                                    </div>
                                    @endif
                                @endcanany

                                @else
                                <div class="p-4 text-center text-muted">
                                    <i class="mdi mdi-folder-open-outline" style="font-size:2rem;"></i>
                                    <p class="mt-2 mb-0">No accounts found for this application.</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- BHM / Billing Actions --}}
                        @canany(['super_admin','bhm'])
                            @if($details->status == 'with-bhm')
                            <div class="card shadow-sm border-0 rounded-lg mb-4">
                                <div class="card-body d-flex align-items-center" style="gap:12px;">
                                    <i class="mdi mdi-shield-check text-success" style="font-size:1.5rem;"></i>
                                    <div class="flex-grow-1">
                                        <strong>BHM Review</strong>
                                        <small class="text-muted d-block">Approve or reject this application for billing.</small>
                                    </div>
                                    <button wire:click="approveforbilling({{ $details->id }})" class="btn btn-success btn-sm">
                                        <i class="mdi mdi-check mr-1"></i>Approve
                                    </button>
                                    <button wire:click="rejectbacktodtm({{ $details->id }})" class="btn btn-danger btn-sm">
                                        <i class="mdi mdi-close mr-1"></i>Reject
                                    </button>
                                </div>
                            </div>
                            @endif
                        @endcanany

                    </div>{{-- /col-xl-8 --}}

                    {{-- ── RIGHT COLUMN ── --}}
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow-sm border-0 rounded-lg sticky-top" style="top:80px;">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-image-multiple text-info mr-2" style="font-size:1.25rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Supporting Documents</h6>
                            </div>
                            <div class="card-body p-3">

                                @php
                                    $baseUrl = 'https://ipay.ibedc.com:7642/storage/';
                                    function docWidget(string $label, ?string $path, string $baseUrl): void {
                                        if (!$path) {
                                            echo '<p class="text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.08em;font-weight:600;">' . e($label) . '</p>';
                                            echo '<div class="text-muted small py-3 text-center border rounded" style="background:#f8f9fa;"><i class="mdi mdi-file-remove-outline mr-1"></i>No file uploaded</div>';
                                            return;
                                        }
                                        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                        $url = $baseUrl . $path;
                                        echo '<p class="text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.08em;font-weight:600;">' . e($label) . '</p>';
                                        if ($ext === 'pdf') {
                                            echo '<a href="' . e($url) . '" target="_blank" class="btn btn-sm btn-outline-danger w-100" style="border-radius:6px;">'
                                               . '<i class="mdi mdi-file-pdf-box mr-1"></i>View PDF</a>';
                                        } else {
                                            echo '<a href="' . e($url) . '" target="_blank">'
                                               . '<img src="' . e($url) . '" class="img-fluid rounded w-100" style="max-height:200px;object-fit:cover;" alt="' . e($label) . '">'
                                               . '</a>';
                                        }
                                    }
                                @endphp

                                {{-- Landlord Photo --}}
                                <div class="mb-4">
                                    @php docWidget('Landlord Photo', $details->continuation?->landloard_picture, $baseUrl) @endphp
                                </div>

                                <hr class="my-2">

                                {{-- NIN Slip --}}
                                <div class="mb-4">
                                    @php docWidget('NIN Slip', $details->continuation?->nin_slip, $baseUrl) @endphp
                                </div>

                                <hr class="my-2">

                                {{-- CAC Slip --}}
                                <div class="mb-2">
                                    @php docWidget('CAC Slip', $details->continuation?->cac_slip, $baseUrl) @endphp
                                </div>

                            </div>
                        </div>
                    </div>{{-- /col-xl-4 --}}

                </div>{{-- /row --}}
            </div>{{-- /content-wrapper --}}

            {{-- Reject Modal --}}
            @if($showRejectModal)
            <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background:rgba(0,0,0,.5);">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg rounded-lg">
                        <div class="modal-header border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-alert-circle text-danger mr-2" style="font-size:1.3rem;"></i>
                                <h5 class="modal-title mb-0">Reject Account</h5>
                            </div>
                            <button type="button" class="close" wire:click="$set('showRejectModal', false)">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Please provide a reason for rejecting this account. This will be sent to the responsible officer.</p>
                            <label for="rejectComment" class="font-weight-medium">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea id="rejectComment"
                                      wire:model.defer="rejectComment"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Describe the reason for rejection..."></textarea>
                            @error('rejectComment')
                                <small class="text-danger mt-1 d-block"><i class="mdi mdi-alert mr-1"></i>{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$set('showRejectModal', false)">
                                Cancel
                            </button>
                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    wire:click="submitBillingReject"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submitBillingReject">
                                    <i class="mdi mdi-close-circle mr-1"></i>Submit Rejection
                                </span>
                                <span wire:loading wire:target="submitBillingReject">
                                    <i class="mdi mdi-loading mdi-spin mr-1"></i>Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <x-footer />
        </div>{{-- /main-panel --}}
    </div>{{-- /page-body-wrapper --}}
</div>
