<div class="container-fluid page-body-wrapper">
    <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
        <div class="row flex-grow w-100">

            {{-- LEFT SIDE: Tracking Details & Form --}}
            <!-- <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white"> -->
                <div class="col-lg-6 d-flex align-items-start justify-content-center bg-white">
                <div class="auth-form-transparent text-left p-4 w-100" style="max-width: 600px;">
                    
                    <h2 class="text-primary font-weight-bold mb-4">Tracking Details</h2>

                    @if (isset($details))
                        <ul class="list-unstyled mb-4">
                            <li><strong>Tracking ID:</strong> {{ $details->tracking_id }}</li>
                            <li><strong>Lat/Long:</strong> {{ $details->latitude }} | {{ $details->longitude }}</li>
                            <li><strong>Address:</strong> {{ $details->house_no }}  {{ $details->full_address }} </li>
                            <li><strong>Region:</strong> {{ $details->region }} </li>
                            <li><strong>Business Hub:</strong> {{ $details->business_hub }} </li>
                            <li><strong>Service Center:</strong> {{ $details->service_center }} </li>
                            <li><strong>DSS:</strong> {{ $details->dss }} </li>
                            <li><strong>Lecan Completed Form:</strong> <a href=" {{ $details->lecan_link }}"> VIEW PDF - (LECAN) </a> </li>
                            <li><strong>Generated Account:</strong> {{ $details->account_no }} </li>
                            <li><strong>Validated By:</strong> {{ \App\Models\User::where('id', $details->validated_by)->value('name') ?? '' }} </li>
                            <li><strong>Status:</strong> {{ $details->status == 0 ? 'Pending' : 'Completed' }} </li>
                        </ul>
                    @endif

                    {{-- Error message --}}
                    @if (session()->has('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                     @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif


                         
                                                                     
                      

                    @canany(['super_admin', 'dtm'])

                     <form wire:submit.prevent="submit">

                      
                         <!-- Business Hub Dropdown -->
                        <div class="form-group">
                            <label>Select Business Hub</label>
                            <select wire:model="selectedBusinesshub" wire:change="updateSelectedBusinesshub($event.target.value)" class="form-control">
                                <option value="">-- Select Business Hub --</option>
                                @foreach ($businesshub as $hub)
                                    <option value="{{ $hub }}">{{ $hub }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Service Center Dropdown -->
                        @if (!is_null($selectedBusinesshub))
                            <div class="form-group">
                                <label>Select Service Center</label>
                                <select wire:model="selectedServicecenter" wire:change="updateSelectedservicecenter($event.target.value)" class="form-control">
                                    <option value="">-- Select Service Center --</option>
                                    @foreach ($servicecenter as $center)
                                        <option value="{{ $center->DSS_11KV_415V_Owner }}">{{ $center->DSS_11KV_415V_Owner }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- DSS Dropdown -->
                        @if ($dss && $dss->count())
                            <div class="form-group">
                                <label>Select DSS</label>
                                <select wire:model="selectedDss" class="form-control">
                                    <option value="">-- Select DSS --</option>
                                    @foreach ($dss as $d)
                                        <option value="{{ $d->Assetid }}">{{ $d->DSS_11KV_415V_Name }} - {{ $d->DSS_11KV_415V_Address }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif


                      
                            <div class="form-group">
                                <label>Add Comment</label>
                                <textarea class="form-control" wire:model="comment"></textarea>
                            </div>
                   


                         <div class="row">
                      <div class="col-md-12">
                        <div class="form-group row">
                        
                          <button class="btn btn-block btn-primary" type="submit">Submit Update</button> 

                        </div>
                      </div>
                     
                    </div>


                    </form>
                      @endcanany

                  
                </div>
            </div>

            {{-- RIGHT SIDE: Branding or Background --}}
            <div class="col-lg-6 d-flex flex-column justify-content-center align-items-center bg-danger text-white p-4">
                <div class="text-center">
                    <h1 class="font-weight-bold"><img src="https://www.ibedc.com/assets/img/logo.png" alt="IBEDC Logo" style="height: 60px;"></h1>
                    <p class="lead">Customer Account Tracking Portal</p>
                    <p class="lead">Customer Building / House</p><br/>

                     @if (isset($details))
                        <img src="/storage/{{ $details->picture }}" alt="{{ $details->tracking_id}}" style="height: 400px;">
                     @endif

                    <p class="mt-5">&copy; {{ date('Y') }} All rights reserved.</p>
                </div>
            </div>

        </div>
    </div>
</div>