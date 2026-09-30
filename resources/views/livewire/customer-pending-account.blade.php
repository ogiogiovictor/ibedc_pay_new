<div wire:poll>

    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">
            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">

                        {{-- =============================== --}}
                        {{-- PAGE HEADER                     --}}
                        {{-- =============================== --}}
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-1 font-weight-bold">NAC Applications Overview</h4>
                                <p class="text-muted mb-0" style="font-size:0.82rem;">
                                    New Account Creation &mdash; tracking dashboard
                                </p>
                            </div>
                            <div class="d-flex align-items-center mt-2 mt-md-0" style="gap:0.5rem;">
                                <span style="display:inline-block; width:9px; height:9px; border-radius:50%; background:#28a745; animation:nac-pulse 2s infinite;"></span>
                                <span class="text-muted" style="font-size:0.8rem;">Live &nbsp;&middot;&nbsp; {{ \Carbon\Carbon::now()->timezone('Africa/Lagos')->format('D, d M Y') }}</span>
                            </div>
                        </div>

                        <style>
                            @keyframes nac-pulse {
                                0%   { box-shadow: 0 0 0 0 rgba(40,167,69,0.5); }
                                70%  { box-shadow: 0 0 0 7px rgba(40,167,69,0); }
                                100% { box-shadow: 0 0 0 0 rgba(40,167,69,0); }
                            }
                            .nac-stat-link .card {
                                transition: transform 0.15s ease, box-shadow 0.15s ease;
                            }
                            .nac-stat-link:hover .card {
                                transform: translateY(-3px);
                                box-shadow: 0 6px 18px rgba(0,0,0,0.1);
                            }
                        </style>

                        {{-- =============================== --}}
                        {{-- SECTION 1: PIPELINE METRICS     --}}
                        {{-- =============================== --}}
                        <p class="text-muted mb-2" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em; font-weight:700;">
                            <i class="mdi mdi-swap-horizontal mr-1"></i> Application Pipeline
                        </p>

                        {{-- Pipeline funnel strip --}}
                        <div class="card mb-3" style="background:#f8f9fa; border:none;">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex align-items-center flex-wrap" style="gap:0;">
                                    @php
                                        $funnel = [
                                            ['label' => 'Started',         'count' => $started ?? 0,               'color' => '#17a2b8'],
                                            ['label' => 'With DTM',        'count' => $pending ?? 0,               'color' => '#ffc107'],
                                            ['label' => 'Reg. Billing',    'count' => $with_regional_billing ?? 0, 'color' => '#17a2b8'],
                                            ['label' => 'Reg. Head',       'count' => $with_region ?? 0,           'color' => '#6f42c1'],
                                            ['label' => 'With Billing',    'count' => $withbilling ?? 0,           'color' => '#fd7e14'],
                                            ['label' => 'Completed',       'count' => $completed ?? 0,             'color' => '#28a745'],
                                        ];
                                    @endphp
                                    @foreach($funnel as $i => $step)
                                        <div class="d-flex align-items-center">
                                            <div class="text-center px-3 py-1">
                                                <div class="font-weight-bold" style="font-size:1.1rem; color:{{ $step['color'] }};">{{ number_format($step['count']) }}</div>
                                                <div style="font-size:0.68rem; color:#6c757d; white-space:nowrap;">{{ $step['label'] }}</div>
                                            </div>
                                            @if(!$loop->last)
                                                <i class="mdi mdi-chevron-right" style="font-size:1.3rem; color:#ced4da;"></i>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            {{-- Started --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <a href="{{ route('records.status', 'started') }}" class="w-100 nac-stat-link" style="text-decoration:none;">
                                    <div class="card h-100" style="border-left:4px solid #17a2b8;">
                                        <div class="card-body py-3 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Started</p>
                                                    <h3 class="mb-0 font-weight-bold text-info">{{ $started ?? 0 }}</h3>
                                                    <small class="text-muted">New applications</small>
                                                </div>
                                                <i class="mdi mdi-play-circle-outline text-info" style="font-size:2rem; opacity:0.35;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            {{-- With DTM --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <a href="{{ route('records.status', 'with-dtm') }}" class="w-100 nac-stat-link" style="text-decoration:none;">
                                    <div class="card h-100" style="border-left:4px solid #ffc107;">
                                        <div class="card-body py-3 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">With DTM</p>
                                                    <h3 class="mb-0 font-weight-bold text-warning">{{ $pending ?? 0 }}</h3>
                                                    <small class="text-muted">Awaiting DTM</small>
                                                </div>
                                                <i class="mdi mdi-account-clock text-warning" style="font-size:2rem; opacity:0.35;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            {{-- Regional Billing --}}
                            @canany(['super_admin', 'billing', 'region'])
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <a href="{{ route('records.status', 'with-regional-billing') }}" class="w-100 nac-stat-link" style="text-decoration:none;">
                                    <div class="card h-100" style="border-left:4px solid #17a2b8;">
                                        <div class="card-body py-3 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Regional Billing</p>
                                                    <h3 class="mb-0 font-weight-bold text-info">{{ $with_regional_billing ?? 0 }}</h3>
                                                    <small class="text-muted">Reg. office queue</small>
                                                </div>
                                                <i class="mdi mdi-office-building text-info" style="font-size:2rem; opacity:0.35;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            {{-- Regional Head --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <a href="{{ route('records.status', 'with-regional-head') }}" class="w-100 nac-stat-link" style="text-decoration:none;">
                                    <div class="card h-100" style="border-left:4px solid #6f42c1;">
                                        <div class="card-body py-3 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Regional Head</p>
                                                    <h3 class="mb-0 font-weight-bold" style="color:#6f42c1;">{{ $with_region ?? 0 }}</h3>
                                                    <small class="text-muted">RH approved</small>
                                                </div>
                                                <i class="mdi mdi-account-tie" style="font-size:2rem; opacity:0.35; color:#6f42c1;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            @endcanany

                            {{-- With Billing --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <a href="{{ route('records.status', 'with-billing') }}" class="w-100 nac-stat-link" style="text-decoration:none;">
                                    <div class="card h-100" style="border-left:4px solid #fd7e14;">
                                        <div class="card-body py-3 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">With Billing</p>
                                                    <h3 class="mb-0 font-weight-bold" style="color:#fd7e14;">{{ $withbilling ?? 0 }}</h3>
                                                    <small class="text-muted">Acct. generation</small>
                                                </div>
                                                <i class="mdi mdi-file-document-edit-outline" style="font-size:2rem; opacity:0.35; color:#fd7e14;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            {{-- Completed --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <a href="{{ route('records.status', 'completed') }}" class="w-100 nac-stat-link" style="text-decoration:none;">
                                    <div class="card h-100" style="border-left:4px solid #28a745;">
                                        <div class="card-body py-3 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Completed</p>
                                                    <h3 class="mb-0 font-weight-bold text-success">{{ $completed ?? 0 }}</h3>
                                                    <small class="text-muted">Accounts active</small>
                                                </div>
                                                <i class="mdi mdi-check-circle-outline text-success" style="font-size:2rem; opacity:0.35;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                        </div>
                        {{-- End Pipeline Row --}}


                        {{-- =============================== --}}
                        {{-- SECTION 2: METER & PAYMENT      --}}
                        {{-- =============================== --}}
                        <p class="text-muted mb-2" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em; font-weight:700;">
                            <i class="mdi mdi-cash-multiple mr-1"></i> Meter &amp; Payment
                        </p>

                        <div class="row">

                            {{-- Meter Installed --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <div class="card h-100" style="border-left:4px solid #20c997;">
                                    <div class="card-body py-3 px-3">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div>
                                                <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Meter Installed</p>
                                                <h3 class="mb-0 font-weight-bold" style="color:#20c997;">{{ $paidformeter ?? 0 }}</h3>
                                                <small class="text-muted">Paid for meter</small>
                                            </div>
                                            <i class="mdi mdi-lightning-bolt" style="font-size:2rem; opacity:0.35; color:#20c997;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pending Payment --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <div class="card h-100" style="border-left:4px solid #ffc107;">
                                    <div class="card-body py-3 px-3">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div>
                                                <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Pending Payment</p>
                                                <h3 class="mb-0 font-weight-bold text-warning">{{ $pendingpayment ?? 0 }}</h3>
                                                <small class="text-muted">Awaiting payment</small>
                                            </div>
                                            <i class="mdi mdi-clock-alert-outline text-warning" style="font-size:2rem; opacity:0.35;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Compliance --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <a href="{{ route('records.status', 'with-compliance') }}" class="w-100 nac-stat-link" style="text-decoration:none;">
                                    <div class="card h-100" style="border-left:4px solid #6f42c1;">
                                        <div class="card-body py-3 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Compliance</p>
                                                    <h3 class="mb-0 font-weight-bold" style="color:#6f42c1;">{{ $withcompliance ?? 0 }}</h3>
                                                    <small class="text-muted">Under review</small>
                                                </div>
                                                <i class="mdi mdi-shield-check-outline" style="font-size:2rem; opacity:0.35; color:#6f42c1;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            {{-- Rejected --}}
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2 grid-margin stretch-card">
                                <a href="{{ route('records.status', 'rejected') }}" class="w-100 nac-stat-link" style="text-decoration:none;">
                                    <div class="card h-100" style="border-left:4px solid #dc3545;">
                                        <div class="card-body py-3 px-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="text-muted mb-1" style="font-size:0.68rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Rejected</p>
                                                    <h3 class="mb-0 font-weight-bold text-danger">{{ $rejected ?? 0 }}</h3>
                                                    <small class="text-muted">Declined</small>
                                                </div>
                                                <i class="mdi mdi-close-circle-outline text-danger" style="font-size:2rem; opacity:0.35;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                        </div>
                        {{-- End Meter & Payment Row --}}


                        {{-- =============================== --}}
                        {{-- SECTION 3: TABLE                --}}
                        {{-- =============================== --}}
                        <div class="row">
                            <div class="col-12 grid-margin">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                            <h5 class="card-title mb-0">
                                                <i class="mdi mdi-table-search mr-1"></i> Summary Dashboard
                                            </h5>
                                            @if(!empty($accounts['total']))
                                                <span class="text-muted" style="font-size:0.8rem;">
                                                    Showing <strong>{{ $accounts['from'] ?? 0 }}–{{ $accounts['to'] ?? 0 }}</strong>
                                                    of <strong>{{ number_format($accounts['total']) }}</strong> records
                                                </span>
                                            @endif
                                        </div>

                                        @if (session()->has('error'))
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                {{ session('error') }}
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                        @endif

                                        <form wire:submit.prevent="searchTransactions">
                                            <div class="d-flex flex-wrap align-items-end" style="gap:0.75rem 1rem;">
                                                <div>
                                                    <label class="mb-1" style="font-size:0.75rem; font-weight:600; color:#6c757d;">From</label>
                                                    <input type="date" class="form-control form-control-sm" wire:model="fromdate" style="min-width:130px;">
                                                </div>
                                                <div>
                                                    <label class="mb-1" style="font-size:0.75rem; font-weight:600; color:#6c757d;">To</label>
                                                    <input type="date" class="form-control form-control-sm" wire:model="todate" style="min-width:130px;">
                                                </div>
                                                <div>
                                                    <label class="mb-1" style="font-size:0.75rem; font-weight:600; color:#6c757d;">Filter By</label>
                                                    <select class="form-control form-control-sm" wire:model="clearOption" style="min-width:130px;">
                                                        <option value="">Select field</option>
                                                        <option value="tracking_id">Tracking ID</option>
                                                        <option value="surname">Surname</option>
                                                        <option value="status">Status</option>
                                                        <option value="email">Email</option>
                                                        <option value="account_no">Account No</option>
                                                        <option value="map_id">Map ID</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="mb-1" style="font-size:0.75rem; font-weight:600; color:#6c757d;">Value</label>
                                                    <input type="text" class="form-control form-control-sm" wire:model="clearValue" placeholder="Enter value" style="min-width:160px;">
                                                </div>
                                                <div class="d-flex" style="gap:0.5rem;">
                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                        <i class="mdi mdi-magnify mr-1"></i>Search
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="exportTransactions">
                                                        <i class="mdi mdi-download mr-1"></i>Export
                                                    </button>
                                                </div>
                                            </div>
                                        </form>

                                        <div class="d-flex flex-wrap align-items-center mt-3 mb-2" style="gap:1rem;">
                                            <span style="font-size:0.72rem; font-weight:600; color:#6c757d; text-transform:uppercase; letter-spacing:0.05em;">Row legend:</span>
                                            <span class="d-flex align-items-center" style="gap:0.35rem; font-size:0.78rem;">
                                                <span style="display:inline-block; width:12px; height:12px; border-radius:2px; background:#d4edda;"></span> Evaluated &amp; Approved
                                            </span>
                                            <span class="d-flex align-items-center" style="gap:0.35rem; font-size:0.78rem;">
                                                <span style="display:inline-block; width:12px; height:12px; border-radius:2px; background:#cce5ff;"></span> Evaluated (Yes)
                                            </span>
                                            <span class="d-flex align-items-center" style="gap:0.35rem; font-size:0.78rem;">
                                                <span style="display:inline-block; width:12px; height:12px; border-radius:2px; background:#f8f9fa; border:1px solid #dee2e6;"></span> Not Evaluated
                                            </span>
                                            <span class="ml-auto d-flex align-items-center" style="gap:1rem; font-size:0.78rem;">
                                                <span style="color:#6c757d;">Age:</span>
                                                <span style="color:#6c757d;">&lt; 3 days</span>
                                                <span style="color:#fd7e14; font-weight:600;">3–6 days</span>
                                                <span style="color:#dc3545; font-weight:600;">&ge; 7 days</span>
                                            </span>
                                        </div>
                                        <hr class="mt-2 mb-3" />

                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover center-aligned-table">
                                                <thead style="position:sticky; top:0; z-index:2; background:#fff;">
                                                    <tr>
                                                        <th>Applied Date</th>
                                                        <th>Tracking No.</th>
                                                        <th>Customer Name</th>
                                                        <th>Phone</th>
                                                        <th>Region</th>
                                                        <th>B/Hub</th>
                                                        <th>S/Center</th>
                                                        <th>Status</th>
                                                        <th>Account</th>
                                                        <th>Evaluated</th>
                                                        <th>Metered</th>
                                                        <th>Age</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @if(count($accounts['links']) > 0)

                                                        @foreach($accounts['data'] as $transaction)
                                                        <tr class="{{
                                                            $transaction['evaluated'] === 'approved' ? 'table-success' : (
                                                            $transaction['evaluated'] === 'yes'      ? 'table-primary' : (
                                                            $transaction['evaluated'] === 'no'       ? 'table-light'   : ''
                                                        )) }}">
                                                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($transaction['created_at'])->timezone('Africa/Lagos')->format('Y-m-d H:i') }}</td>
                                                            <td><strong>{{ $transaction['tracking_id'] }}</strong></td>
                                                            <td class="text-nowrap">{{ $transaction['customer']['surname'] }} {{ $transaction['customer']['firstname'] }} {{ $transaction['customer']['other_name'] }}</td>
                                                            <td>{{ $transaction['customer']['phone'] }}</td>
                                                            <td>{{ $transaction['region'] }}</td>
                                                            <td>{{ $transaction['business_hub'] }}</td>
                                                            <td>{{ $transaction['service_center'] }}</td>
                                                            <td>
                                                                @if($transaction['status'] == "0")
                                                                    <span class="badge badge-info">Started</span>
                                                                @elseif($transaction['status'] == "1")
                                                                    <span class="badge badge-warning">With DTM</span>
                                                                @elseif($transaction['status'] == "3")
                                                                    <span class="badge" style="background-color:#6f42c1; color:#fff;">Compliance</span>
                                                                @elseif($transaction['status'] == "2" && $transaction['evaluated'] == "yes")
                                                                    <span class="badge badge-warning">With Billing</span>
                                                                @elseif($transaction['status'] == "2" && $transaction['evaluated'] == "approved")
                                                                    <span class="badge badge-info">With RH</span>
                                                                @elseif($transaction['status'] == "2" && $transaction['evaluated'] == "no")
                                                                    <span class="badge badge-secondary">Reg. Billing</span>
                                                                @elseif($transaction['status'] == "2")
                                                                    <span class="badge badge-warning">With Billing</span>
                                                                @elseif($transaction['status'] == "4")
                                                                    <span class="badge badge-success">Completed</span>
                                                                @elseif($transaction['status'] == "5")
                                                                    <span class="badge badge-danger">Rejected</span>
                                                                @elseif($transaction['status'] == "6")
                                                                    <span class="badge badge-dark">Staged</span>
                                                                @else
                                                                    <span class="badge badge-light">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $transaction['account_no'] }}</td>
                                                            <td>
                                                                @if($transaction['evaluated'] === 'approved')
                                                                    <span class="badge badge-success">Approved</span>
                                                                @elseif($transaction['evaluated'] === 'yes')
                                                                    <span class="badge badge-primary">Yes</span>
                                                                @elseif($transaction['evaluated'] === 'no')
                                                                    <span class="badge badge-secondary">No</span>
                                                                @else
                                                                    <span class="badge badge-light">{{ $transaction['evaluated'] }}</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($transaction['paid_for_meter'] === 'Yes')
                                                                    <span class="badge badge-success">Yes</span>
                                                                @elseif($transaction['paid_for_meter'] === 'No')
                                                                    <span class="badge badge-danger">No</span>
                                                                @elseif($transaction['paid_for_meter'] === 'Old')
                                                                    <span class="badge badge-secondary">Old</span>
                                                                @else
                                                                    <span class="badge badge-light">—</span>
                                                                @endif
                                                            </td>
                                                            @php
                                                                $ageDate  = $transaction['status'] == '0'
                                                                    ? \Carbon\Carbon::parse($transaction['created_at'])
                                                                    : \Carbon\Carbon::parse($transaction['updated_at']);
                                                                $ageDays  = $ageDate->diffInDays(\Carbon\Carbon::now());
                                                                $ageColor = $ageDays >= 7 ? '#dc3545' : ($ageDays >= 3 ? '#fd7e14' : '#6c757d');
                                                                $ageBold  = $ageDays >= 3 ? 'font-weight:600;' : '';
                                                            @endphp
                                                            <td class="text-nowrap" style="font-size:0.8rem; color:{{ $ageColor }}; {{ $ageBold }}">
                                                                {{ $ageDate->diffForHumans() }}
                                                            </td>
                                                            <td>
                                                                @canany(['super_admin', 'dtm', 'billing', 'bhm', 'rico', 'audit', 'isviewonly', 'region'])
                                                                    <a href="account_details/{{ $transaction['tracking_id'] }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="View details">
                                                                        <i class="mdi mdi-eye-outline"></i>
                                                                    </a>
                                                                @endcanany
                                                                @canany(['super_admin', 'mso'])
                                                                @endcanany
                                                            </td>
                                                        </tr>
                                                        @endforeach

                                                        <tr>
                                                            <td colspan="13" class="pt-3 pb-1 border-0">
                                                                <nav>
                                                                    <ul class="pagination pagination-sm justify-content-end mb-0">
                                                                        @foreach($accounts['links'] as $link)
                                                                            <li class="page-item {{ $link['active'] ? 'active' : '' }} {{ is_null($link['url']) ? 'disabled' : '' }}">
                                                                                <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </nav>
                                                            </td>
                                                        </tr>

                                                    @else
                                                        <tr>
                                                            <td colspan="13" class="text-center py-5 text-muted">
                                                                <i class="mdi mdi-folder-open-outline d-block mb-1" style="font-size:2.5rem;"></i>
                                                                No records found
                                                            </td>
                                                        </tr>
                                                    @endif

                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- End Table Section --}}

                    </div>
                </div>
            </div>

            <x-footer />
        </div>
    </div>

</div>
