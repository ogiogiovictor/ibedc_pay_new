<div class="container-fluid page-body-wrapper">
    <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
        <div class="row flex-grow w-100">

            {{-- LEFT SIDE: Tracking Details & Form --}}
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white">
                <div class="auth-form-transparent text-left p-4 w-100" style="max-width: 600px;">
                    <div class="text-center mb-4">
                        <img src="https://www.ibedc.com/assets/img/logo.png" alt="IBEDC Logo" style="height: 60px;">
                    </div>

                    <h2 class="text-primary font-weight-bold mb-4">Tracking Details</h2>

                    @if (isset($tracking))
                        <ul class="list-unstyled mb-4">
                            <li><strong>Tracking ID:</strong> {{ $tracking->tracking_id }}</li>
                            <li><strong>Surname:</strong> {{ $tracking->surname }}</li>
                            <li><strong>Firstname:</strong> {{ $tracking->firstname }}</li>
                            <li><strong>Other Name:</strong> {{ $tracking->other_name }}</li>
                            <li><strong>House No:</strong> {{ $tracking->house_no }}</li>
                            <li><strong>Address:</strong> {{ $tracking->address }}</li>
                            <li><strong>Lat/Long:</strong> {{ $tracking->continuation->latitude }} | {{ $tracking->continuation->longitude }}</li>
                            <li><strong>Accounts Requested:</strong> {{ $tracking->no_of_account_apply_for }}</li>
                            <li><strong>Status:</strong> {{ ucfirst($tracking->status) }}</li>
                        </ul>
                    @endif

                    {{-- Error message --}}
                    @if (session()->has('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form wire:submit.prevent="submit">
                        <div class="form-group">
                            <label for="validation_code">Enter Validation Code</label>
                            <input type="text" wire:model="form.validationcode" id="validation_code" class="form-control" placeholder="Validation code">
                            @error('validationcode')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>


                        <div class="form-group">
                            <label>Select Business Hub</label>
                            <select wire:model="selectedBusinesshub" class="form-control">
                                <option value="">-- Select Business Hub --</option>
                                @foreach ($businesshub as $hub)
                                    <option value="{{$hub}}">{{ $hub }}</option>
                                @endforeach
                            </select>
                            <input type="text" wire:model="form.businesshub" class="form-control">
                        </div>

                       
                        @if (!is_null($selectedBusinesshub))
                        <div class="form-group">
                            <label>Select Service Center</label>
                            <select wire:model="selectedServicecenter" class="form-control">
                                <option value="">-- Select Service Center --</option>
                                
                                @foreach ($servicecenter as $center)
                                
                                    <option value="{{ $center->Assetid }}">{{ $center->DSS_11KV_415V_Owner }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                         @if (!is_null($selectedState))
                        <div class="form-group">
                            <label>Select DSS</label>
                            <select wire:model="selectedDss" class="form-control">
                                <option value="">-- Select DSS --</option>
                                @foreach ($dss as $dss)
                                    <option value="{{ $dss->DSS_11KV_415V_Owner }}">{{ $dss->DSS_11KV_415V_Owner }}</option>
                                @endforeach
                            </select>
                        </div>
                         @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-block">Track</button>
                            <span wire:loading class="d-block mt-2 text-muted">Tracking Application...</span>
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT SIDE: Branding or Background --}}
            <div class="col-lg-6 d-flex flex-column justify-content-center align-items-center bg-primary text-white p-4">
                <div class="text-center">
                    <h1 class="font-weight-bold">IBEDC</h1>
                    <p class="lead">Customer Account Tracking Portal</p>
                    <p class="mt-5">&copy; {{ date('Y') }} All rights reserved.</p>
                </div>
            </div>

        </div>
    </div>
</div>