<div>
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">
            <div class="content-wrapper">

                {{-- Page Header --}}
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h4 class="font-weight-bold mb-1">Account Tracking</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0 bg-transparent">
                                <li class="breadcrumb-item"><a href="/new_account" wire:navigation>New Accounts</a></li>
                                <li class="breadcrumb-item active text-primary">{{ $details->tracking_id ?? '—' }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex align-items-center mt-1" style="gap:8px;">
                        @if(isset($details))
                        @php
                            $sMap = [
                                '0' => ['label' => 'Started',         'cls' => 'secondary'],
                                '1' => ['label' => 'With DTM',        'cls' => 'warning'],
                                '2' => ['label' => 'With Billing',    'cls' => 'primary'],
                                '3' => ['label' => 'Compliance',      'cls' => 'purple'],
                                '4' => ['label' => 'Completed',       'cls' => 'success'],
                                '5' => ['label' => 'Rejected',        'cls' => 'danger'],
                                '6' => ['label' => 'Staged',          'cls' => 'dark'],
                            ];
                            $s = $sMap[(string)$details->status] ?? ['label' => 'N/A', 'cls' => 'light'];
                        @endphp
                        <span class="badge badge-{{ $s['cls'] }} px-3 py-2" style="font-size:.82rem;">
                            {{ $s['label'] }}
                        </span>
                        @endif
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
                            <i class="mdi mdi-arrow-left mr-1"></i>Back
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

                @if(isset($details))
                <div class="row">

                    {{-- ── LEFT COLUMN ── --}}
                    <div class="col-lg-8">

                        {{-- Location & Property --}}
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-map-marker text-danger mr-2" style="font-size:1.2rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Location &amp; Property</h6>
                            </div>
                            <div class="card-body pt-4">
                                <div class="row">
                                    <div class="col-sm-12 mb-3">
                                        <small class="text-muted d-block">Full Address</small>
                                        <span class="font-weight-medium">{{ $details->house_no }} {{ $details->full_address ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">LGA</small>
                                        <span class="font-weight-medium">{{ $details->lga ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Nearest Bus Stop</small>
                                        <span class="font-weight-medium">{{ $details->nearest_bustop ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Landmark</small>
                                        <span class="font-weight-medium">{{ $details->landmark ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Type of Premise</small>
                                        <span class="font-weight-medium">{{ $details->type_of_premise ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Use of Premise</small>
                                        <span class="font-weight-medium">{{ $details->use_of_premise ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Coordinates</small>
                                        <span class="font-weight-medium" style="font-size:.82rem;">
                                            {{ $details->latitude }}, {{ $details->longitude }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Network & Assignment --}}
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-transmission-tower text-warning mr-2" style="font-size:1.2rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Network &amp; Assignment</h6>
                            </div>
                            <div class="card-body pt-4">
                                <div class="row">
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Region</small>
                                        <span class="font-weight-medium">{{ $details->region ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Business Hub</small>
                                        <span class="font-weight-medium">{{ $details->business_hub ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Service Center</small>
                                        <span class="font-weight-medium">{{ $details->service_center ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">DSS</small>
                                        <span class="font-weight-medium">{{ $details->dss ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Tariff</small>
                                        <span class="font-weight-medium">{{ $details->tarrif ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <small class="text-muted d-block">Meter Status</small>
                                        @php $pm = $details->paid_for_meter; @endphp
                                        @if(in_array($pm, ['Yes','Old','Paid']))
                                            <span class="badge badge-success">{{ $pm }}</span>
                                        @else
                                            <span class="badge badge-light border">{{ $pm ?: '—' }}</span>
                                        @endif
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block">Assigned To</small>
                                        <span class="font-weight-medium">
                                            {{ \App\Models\User::where([
                                                'business_hub' => $details->business_hub,
                                                'sc' => $details->service_center
                                            ])->value('email') ?: '—' }}
                                        </span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block">Validated By</small>
                                        <span class="font-weight-medium">{{ $details->validated_by ?: '—' }}</span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block">Generated Account No.</small>
                                        @if($details->account_no)
                                            <code class="text-success font-weight-bold">{{ $details->account_no }}</code>
                                        @else
                                            <span class="text-muted">Not yet generated</span>
                                        @endif
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block">Meter No.</small>
                                        @if($details->meterno)
                                            <code class="text-success font-weight-bold">{{ $details->meterno }}</code>
                                        @else
                                            <span class="text-muted">Not Meter Attached to Account</span>
                                        @endif
                                    </div>

                                     <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block">Programme</small>
                                        @if($details->programme)
                                            <code class="text-success font-weight-bold">{{ $details->programme }}</code>
                                        @else
                                            <span class="text-muted">Not Programmed/Applicable</span>
                                        @endif
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <small class="text-muted d-block">LECAN Form</small>
                                        @if($details->lecan_link)
                                            @php $lecanExt = strtolower(pathinfo($details->lecan_link, PATHINFO_EXTENSION)); @endphp
                                            @if($lecanExt === 'pdf')
                                                <a href="/storage/{{ $details->lecan_link }}" target="_blank" class="btn btn-xs btn-outline-danger">
                                                    <i class="mdi mdi-file-pdf-box mr-1"></i>View PDF
                                                </a>
                                            @else
                                                <a href="/storage/{{ $details->lecan_link }}" target="_blank">
                                                    <img src="/storage/{{ $details->lecan_link }}"
                                                         alt="LECAN Form"
                                                         class="img-fluid rounded"
                                                         style="max-height:120px;object-fit:cover;">
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">Not uploaded</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Comments --}}
                        @if($details->comment || $details->billing_comment)
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-comment-text-outline text-info mr-2" style="font-size:1.2rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Comments</h6>
                            </div>
                            <div class="card-body pt-3">
                                @if($details->comment)
                                <div class="mb-3">
                                    <small class="text-muted text-uppercase" style="font-size:.68rem;letter-spacing:.06em;font-weight:600;">DTM Comment</small>
                                    <div class="p-3 mt-1 rounded" style="background:#f8f9fa;border-left:3px solid #ffc107;">
                                        {{ $details->comment }}
                                    </div>
                                </div>
                                @endif
                                @if($details->billing_comment)
                                <div class="mb-1">
                                    <small class="text-muted text-uppercase" style="font-size:.68rem;letter-spacing:.06em;font-weight:600;">Billing Comment</small>
                                    <div class="p-3 mt-1 rounded" style="background:#f8f9fa;border-left:3px solid #fd7e14;">
                                        {{ $details->billing_comment }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Audit Logs --}}
                        @if($logs && $logs->count() > 0)
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-history text-secondary mr-2" style="font-size:1.2rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Audit Log
                                    <span class="badge badge-light border ml-2">{{ $logs->count() }}</span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>User</th>
                                                <th>Module</th>
                                                <th>Comment</th>
                                                <th>Type</th>
                                                <th class="text-nowrap">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($logs as $log)
                                            @php
                                                $typeColors = [
                                                    'Approved'  => 'success',
                                                    'Rejected'  => 'danger',
                                                    'Moved'     => 'info',
                                                    'Completed' => 'primary',
                                                ];
                                                $tc = $typeColors[$log->type] ?? 'secondary';
                                                $initials = strtoupper(substr($log->user_email ?? 'U', 0, 1));
                                            @endphp
                                            <tr>
                                                <td class="text-nowrap">
                                                    <div class="d-flex align-items-center" style="gap:6px;">
                                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border font-weight-bold text-secondary"
                                                              style="width:26px;height:26px;font-size:.72rem;flex-shrink:0;">
                                                            {{ $initials }}
                                                        </span>
                                                        <span style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->user_email }}">
                                                            {{ $log->user_email }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>{{ $log->module }}</td>
                                                <td style="max-width:200px;white-space:normal;">{{ $log->comment }}</td>
                                                <td><span class="badge badge-{{ $tc }}">{{ $log->type }}</span></td>
                                                <td class="text-nowrap text-muted">
                                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Compliance Form --}}
                        @canany(['super_admin', 'rico'])
                        @if($details->status == "3")
                        <div class="card shadow-sm border-0 rounded-lg mb-4" style="border-left:4px solid #6f42c1 !important;">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-shield-alert text-purple mr-2" style="font-size:1.2rem;color:#6f42c1;"></i>
                                <h6 class="mb-0 font-weight-semibold" style="color:#6f42c1;">Compliance Review</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    This application is pending compliance review. Submit a rejection to route it back to DTM or the customer.
                                </p>
                                <form wire:submit.prevent="compliancereject">
                                    <div class="form-group">
                                        <label class="font-weight-medium">Rejection Reason <span class="text-danger">*</span></label>
                                        <textarea class="form-control" wire:model="rcomment" rows="3"
                                                  placeholder="Describe the compliance issue..."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="font-weight-medium">Route To <span class="text-danger">*</span></label>
                                        <select wire:model="action" class="form-control">
                                            <option value="">— Select —</option>
                                            <option value="dtm">DTM</option>
                                            <option value="customer">Customer</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-danger btn-sm" type="submit">
                                        <i class="mdi mdi-close-circle mr-1"></i>Submit Rejection
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif
                        @endcanany

                        {{-- Admin Controls --}}
                        @canany(['super_admin'])
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-cog text-dark mr-2" style="font-size:1.2rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Administrator Controls</h6>
                                <span class="badge badge-dark ml-2" style="font-size:.68rem;">Super Admin</span>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    Re-map this account to a different Business Hub, Service Center, and DSS. All fields are required.
                                </p>
                                <form wire:submit.prevent="submit">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-medium">Business Hub</label>
                                                <select wire:model="selectedBusinesshub"
                                                        wire:change="updateSelectedBusinesshub($event.target.value)"
                                                        class="form-control">
                                                    <option value="">— Select Business Hub —</option>
                                                    @foreach($businesshub as $hub)
                                                    <option value="{{ $hub }}">{{ $hub }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        @if(!is_null($selectedBusinesshub))
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-medium">Service Center</label>
                                                <select wire:model="selectedServicecenter"
                                                        wire:change="updateSelectedservicecenter($event.target.value)"
                                                        class="form-control">
                                                    <option value="">— Select Service Center —</option>
                                                    @foreach($servicecenter as $center)
                                                    <option value="{{ $center->DSS_11KV_415V_Owner }}">{{ $center->DSS_11KV_415V_Owner }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif

                                        @if($dss && $dss->count())
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-medium">DSS</label>
                                                <select wire:model="selectedDss" class="form-control">
                                                    <option value="">— Select DSS —</option>
                                                    @foreach($dss as $d)
                                                    <option value="{{ $d->Assetid }}">{{ $d->DSS_11KV_415V_Name }} — {{ $d->DSS_11KV_415V_Address }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-medium">New Tariff</label>
                                                <select wire:model="newTarriff" class="form-control">
                                                    <option value="">— Select New Tariff —</option>
                                                    @foreach($tarriff as $t)
                                                    <option value="{{ $t->TariffCode }}">{{ $t->TariffCode }} — {{ $t->Description }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-medium">Old Tariff</label>
                                                <select wire:model="oldTarriff" class="form-control">
                                                    <option value="">— Select Old Tariff —</option>
                                                    @foreach($tarriff as $o)
                                                    <option value="{{ $o->TariffID }}">{{ $o->OldTariffCode }} — {{ $o->Description }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-medium">Band</label>
                                                <select wire:model="band" class="form-control">
                                                    <option value="">— Select Band —</option>
                                                    @foreach($iband as $b)
                                                    <option value="{{ $b->ServiceID }}">{{ $b->ServiceID }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="font-weight-medium">Comment</label>
                                                <textarea class="form-control" wire:model="comment" rows="2"
                                                          placeholder="Optional note about this update..."></textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <button class="btn btn-primary btn-sm" type="submit"
                                                    wire:loading.attr="disabled" wire:target="submit">
                                                <span wire:loading.remove wire:target="submit">
                                                    <i class="mdi mdi-content-save mr-1"></i>Submit Update
                                                </span>
                                                <span wire:loading wire:target="submit">
                                                    <i class="mdi mdi-loading mdi-spin mr-1"></i>Saving...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endcanany

                    </div>{{-- /col-lg-8 --}}

                    {{-- ── RIGHT COLUMN ── --}}
                    <div class="col-lg-4">

                        {{-- Building Photo --}}
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center bg-white border-bottom py-3">
                                <i class="mdi mdi-home-city text-primary mr-2" style="font-size:1.2rem;"></i>
                                <h6 class="mb-0 font-weight-semibold">Building / House Photo</h6>
                            </div>
                            <div class="card-body p-0">
                                @if($details->picture)
                                    @php $picExt = strtolower(pathinfo($details->picture, PATHINFO_EXTENSION)); @endphp
                                    @if($picExt === 'pdf')
                                        <div class="p-4 text-center">
                                            <a href="/storage/{{ $details->picture }}" target="_blank" class="btn btn-outline-danger">
                                                <i class="mdi mdi-file-pdf-box mr-1"></i>View PDF
                                            </a>
                                        </div>
                                    @else
                                        <a href="/storage/{{ $details->picture }}" target="_blank">
                                            <img src="/storage/{{ $details->picture }}"
                                                 alt="{{ $details->tracking_id }}"
                                                 class="w-100 rounded-bottom"
                                                 style="max-height:320px;object-fit:cover;">
                                        </a>
                                    @endif
                                @else
                                <div class="d-flex flex-column align-items-center justify-content-center text-muted py-5">
                                    <i class="mdi mdi-image-off-outline" style="font-size:2.5rem;"></i>
                                    <small class="mt-2">No photo uploaded</small>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Map --}}
                        @if($details->latitude && $details->longitude)
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-header d-flex align-items-center justify-content-between bg-white border-bottom py-3">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-map-marker text-danger mr-2" style="font-size:1.2rem;"></i>
                                    <h6 class="mb-0 font-weight-semibold">Building Location</h6>
                                </div>
                                <a href="https://www.google.com/maps?q={{ $details->latitude }},{{ $details->longitude }}"
                                   target="_blank"
                                   class="btn btn-xs btn-outline-primary"
                                   title="Open in Google Maps">
                                    <i class="mdi mdi-google-maps mr-1"></i>Google Maps
                                </a>
                            </div>
                            <div class="card-body p-0" style="position:relative;">
                                <div id="map" style="height:380px;width:100%;"></div>
                            </div>
                            <div class="card-footer bg-white border-top py-2 px-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">
                                        <i class="mdi mdi-crosshairs-gps mr-1 text-danger"></i>
                                        <strong>{{ $details->latitude }}</strong>, <strong>{{ $details->longitude }}</strong>
                                    </small>
                                    <small>
                                        <button onclick="window.copyCoords()" title="Copy coordinates"
                                                style="background:none;border:none;cursor:pointer;color:#888;padding:0;font-size:.78rem;">
                                            <i class="mdi mdi-content-copy" style="font-size:13px;"></i> Copy
                                        </button>
                                    </small>
                                </div>
                                <div id="map-footer-osm" class="text-muted" style="font-size:.78rem;">
                                    <i class="mdi mdi-loading mdi-spin mr-1"></i>Loading OSM address…
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Quick Info strip --}}
                        <div class="card shadow-sm border-0 rounded-lg mb-4">
                            <div class="card-body py-3 px-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <small class="text-muted text-uppercase" style="font-size:.68rem;font-weight:600;letter-spacing:.06em;">Tracking ID</small>
                                    <span class="font-weight-bold text-primary" style="font-size:.85rem;">{{ $details->tracking_id }}</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <small class="text-muted text-uppercase" style="font-size:.68rem;font-weight:600;letter-spacing:.06em;">Status</small>
                                    <span class="badge badge-{{ $s['cls'] }}">{{ $s['label'] }}</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-muted text-uppercase" style="font-size:.68rem;font-weight:600;letter-spacing:.06em;">Map ID</small>
                                    <span class="font-weight-medium" style="font-size:.85rem;">{{ $details->map_id ?: '—' }}</span>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /col-lg-4 --}}

                </div>{{-- /row --}}
                @else
                <div class="alert alert-warning">
                    <i class="mdi mdi-alert-outline mr-2"></i>No record found for this tracking ID.
                </div>
                @endif

            </div>{{-- /content-wrapper --}}

            <x-footer />
        </div>{{-- /main-panel --}}
    </div>{{-- /page-body-wrapper --}}

    {{-- Leaflet Map --}}
    @if(isset($details) && $details->latitude && $details->longitude)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        /* Pulsing ring around the building pin */
        .building-pulse {
            position: relative;
            width: 20px;
            height: 20px;
        }
        .building-pulse::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 36px; height: 36px;
            margin: -18px 0 0 -18px;
            border-radius: 50%;
            border: 3px solid #e53e3e;
            animation: pulse-ring 1.8s ease-out infinite;
            opacity: 0;
        }
        @keyframes pulse-ring {
            0%   { transform: scale(0.4); opacity: 0.9; }
            100% { transform: scale(2.2); opacity: 0; }
        }
        .building-pin {
            width: 32px;
            height: 32px;
            background: #e53e3e;
            border: 3px solid #fff;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 3px 10px rgba(0,0,0,0.35);
        }
        .building-pin-inner {
            width: 14px;
            height: 14px;
            background: #fff;
            border-radius: 50%;
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            display: flex; align-items: center; justify-content: center;
        }
        .building-pin-icon {
            font-size: 9px;
            color: #e53e3e;
            transform: rotate(45deg);
            line-height: 1;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lat = {{ $details->latitude }};
            const lng = {{ $details->longitude }};
            const address = @json(trim(($details->house_no ?? '') . ' ' . ($details->full_address ?? '')));
            const trackingId = @json($details->tracking_id);

            /* Global so inline onclick in popup HTML can reach it */
            window.copyCoords = function () {
                navigator.clipboard.writeText(`${lat}, ${lng}`).then(() => {
                    const btn = document.querySelector('[data-copy-coords]');
                    if (btn) { btn.textContent = 'Copied!'; setTimeout(() => btn.textContent = 'Copy', 1500); }
                }).catch(() => {});
            };

            const esri = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 22, attribution: 'Tiles &copy; Esri'
            });
            const osmTile = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 20, attribution: '&copy; OpenStreetMap contributors'
            });

            const map = L.map('map', { center: [lat, lng], zoom: 20, layers: [esri] });

            /* Custom house pin icon */
            const houseIcon = L.divIcon({
                className: '',
                html: `<div class="building-pulse">
                           <div class="building-pin">
                               <div class="building-pin-inner">
                                   <span class="mdi mdi-home building-pin-icon"></span>
                               </div>
                           </div>
                       </div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -34],
            });

            /* 15 m dashed circle — reflects realistic mobile GPS uncertainty */
            L.circle([lat, lng], {
                radius: 15,
                color: '#e53e3e',
                fillColor: '#fc8181',
                fillOpacity: 0.12,
                weight: 2,
                dashArray: '5 5',
            }).addTo(map);

            const marker = L.marker([lat, lng], { icon: houseIcon }).addTo(map);

            /* Re-center control */
            const RecenterControl = L.Control.extend({
                options: { position: 'topleft' },
                onAdd: function () {
                    const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                    const btn = L.DomUtil.create('a', '', container);
                    btn.innerHTML = '<i class="mdi mdi-crosshairs-gps" style="font-size:15px;"></i>';
                    btn.href = '#';
                    btn.title = 'Re-centre on pin';
                    btn.style.cssText = 'display:flex;align-items:center;justify-content:center;width:26px;height:26px;';
                    L.DomEvent.on(btn, 'click', function (e) {
                        L.DomEvent.preventDefault(e);
                        L.DomEvent.stopPropagation(e);
                        map.setView([lat, lng], 20);
                    });
                    return container;
                }
            });
            new RecenterControl().addTo(map);

            /* Fuzzy word-overlap score between customer address and nominatim full text */
            function matchScore(custAddr, nomText) {
                if (!custAddr || !nomText) return null;
                const words = custAddr.toLowerCase().replace(/[^a-z0-9\s]/g, '').split(/\s+/).filter(w => w.length > 2);
                if (!words.length) return null;
                const norm = nomText.toLowerCase();
                return words.filter(w => norm.includes(w)).length / words.length;
            }

            function matchBadge(score) {
                if (score === null) return '';
                if (score >= 0.6) return `<span style="background:#d4edda;color:#155724;font-size:.68rem;padding:1px 7px;border-radius:10px;font-weight:600;">&#10003; Good match</span>`;
                if (score >= 0.3) return `<span style="background:#fff3cd;color:#856404;font-size:.68rem;padding:1px 7px;border-radius:10px;font-weight:600;">&#9888; Partial match</span>`;
                return `<span style="background:#f8d7da;color:#721c24;font-size:.68rem;padding:1px 7px;border-radius:10px;font-weight:600;">&#10007; Mismatch</span>`;
            }

            function buildPopup(nomData) {
                let osmBlock = '';
                let pinTypeBlock = '';

                if (nomData) {
                    const a        = nomData.address || {};
                    const road     = a.road || a.pedestrian || a.residential || '—';
                    const area     = a.suburb || a.neighbourhood || a.quarter || a.village || '—';
                    const city     = a.city || a.town || a.municipality || '—';
                    const state    = a.state || '—';
                    const score    = matchScore(address, nomData.display_name);
                    const badge    = matchBadge(score);
                    const pinType  = nomData.type ? nomData.type.replace(/_/g, ' ') : null;

                    osmBlock = `
                        <div style="margin:6px 0 2px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                <span style="font-size:.7rem;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.04em;">OSM Address</span>
                                ${badge}
                            </div>
                            <table style="font-size:.75rem;width:100%;border-collapse:collapse;">
                                <tr><td style="color:#aaa;padding:1px 8px 1px 0;white-space:nowrap;">Street</td><td style="color:#2d6a4f;">${road}</td></tr>
                                <tr><td style="color:#aaa;padding:1px 8px 1px 0;white-space:nowrap;">Area</td><td style="color:#2d6a4f;">${area}</td></tr>
                                <tr><td style="color:#aaa;padding:1px 8px 1px 0;white-space:nowrap;">City</td><td style="color:#2d6a4f;">${city}</td></tr>
                                <tr><td style="color:#aaa;padding:1px 8px 1px 0;white-space:nowrap;">State</td><td style="color:#2d6a4f;">${state}</td></tr>
                            </table>
                        </div>`;

                    if (pinType) {
                        const isResidential = pinType === 'residential' || pinType === 'house';
                        const bg  = isResidential ? '#d4edda' : '#fff3cd';
                        const clr = isResidential ? '#155724'  : '#856404';
                        pinTypeBlock = `<div style="margin:4px 0 2px;">
                            <span style="font-size:.68rem;color:#888;">Pin landed on: </span>
                            <span style="background:${bg};color:${clr};font-size:.68rem;padding:1px 7px;border-radius:10px;font-weight:600;">${pinType}</span>
                        </div>`;
                    }
                } else {
                    osmBlock = `<div style="margin:6px 0;font-size:.75rem;color:#aaa;font-style:italic;">OSM address unavailable</div>`;
                }

                return `<div style="min-width:220px;">
                            <strong style="font-size:.85rem;">${trackingId}</strong><br>
                            <div style="margin:4px 0 2px;">
                                <span style="font-size:.7rem;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.04em;">Customer Address</span><br>
                                <span style="font-size:.78rem;color:#555;">${address}</span>
                            </div>
                            <hr style="margin:6px 0;">
                            ${osmBlock}
                            ${pinTypeBlock}
                            <hr style="margin:6px 0;">
                            <small style="color:#888;"><b>Lat:</b> ${lat} &nbsp; <b>Lng:</b> ${lng}</small>
                            <button data-copy-coords onclick="window.copyCoords()"
                                    style="background:none;border:none;cursor:pointer;color:#aaa;padding:0;margin-left:6px;font-size:.72rem;vertical-align:middle;">Copy</button><br>
                            <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank"
                               style="font-size:.75rem;color:#4e73df;">
                                Open in Google Maps &rarr;
                            </a>
                        </div>`;
            }

            marker.bindPopup(
                `<div style="min-width:220px;font-size:.8rem;color:#888;"><i class="mdi mdi-loading mdi-spin mr-1"></i>Loading address…</div>`,
                { maxWidth: 310 }
            ).openPopup();

            function updateFooter(nomData) {
                const el = document.getElementById('map-footer-osm');
                if (!el) return;
                if (nomData && nomData.display_name) {
                    el.innerHTML = `<i class="mdi mdi-map-search-outline mr-1 text-success"></i>${nomData.display_name}`;
                } else {
                    el.innerHTML = `<span style="font-style:italic;">OSM address unavailable</span>`;
                }
            }

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`, {
                headers: { 'Accept-Language': 'en', 'User-Agent': 'IBEDC-Pay/1.0' }
            })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                marker.setPopupContent(buildPopup(data));
                updateFooter(data);
            })
            .catch(() => {
                marker.setPopupContent(buildPopup(null));
                updateFooter(null);
            });

            L.control.layers({ 'Satellite': esri, 'Street Map': osmTile }).addTo(map);

            setTimeout(() => map.invalidateSize(), 250);
        });
    </script>
    @endif
</div>
