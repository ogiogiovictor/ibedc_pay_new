<div wire:poll>
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">
            <div class="content-wrapper">
                <div class="row mb-4">
                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            Customer Details for <span class="text-primary">{{ $details->tracking_id }}</span>
                        </h4>
                        <a href="/new_account" class="btn btn-outline-secondary btn-sm" wire:navigation>
                            &laquo; Back
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Left: Transaction Details -->
                    <div class="col-md-8">
                        <div class="card shadow-sm rounded-3">
                            <div class="card-header bg-light text-white">
                                <h6 class="mb-0">Customer Details</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($errorMessage))
                                    <div class="alert alert-danger">
                                        {{ $errorMessage }}
                                    </div>
                                @endif

                                @if(session()->has('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                @if(session()->has('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif

                              

                                 <!-- Grouped Info Layout -->
            <div class="mb-4">
                <h6 class="text-muted mb-3">Basic Information</h6>
                <div class="row">
                    <div class="col-sm-4 mb-3"><strong>Date:</strong> {{ $details->created_at->format('d M, Y H:i') }}</div>
                    <div class="col-sm-4 mb-3"><strong>Title:</strong> {{ $details->title }}</div>
                     <div class="col-sm-4 mb-3"><strong>Surname:</strong> {{ $details->surname }}</div>
                    <div class="col-sm-4 mb-3"><strong>FirstName:</strong> {{ $details->firstname }}</div>
                    <div class="col-sm-4 mb-3"><strong>Other Names:</strong> {{ $details->other_name }}</div>
                    
                    @if (auth()->check() && auth()->user()->authority !== 'dtm')
                        <div class="col-sm-4 mb-3"><strong>Email:</strong> {{ $details->email }}</div>
                        <div class="col-sm-4 mb-3"><strong>Phone:</strong> {{ $details->phone }}</div>
                    @endif


                    <div class="col-sm-4 mb-3"><strong>House No:</strong> {{ $details->house_no }}</div>
                    <div class="col-sm-4 mb-3"><strong>Nearest Bustop:</strong> {{ $details->nearest_bustop }}</div>
                    <div class="col-sm-4 mb-3"><strong>LGA:</strong> {{ $details->lga }}</div>
                    <div class="col-sm-4 mb-3"><strong>Full Address:</strong> {{ $details->address }}</div>
                    <div class="col-sm-4 mb-3"><strong>Type Of Premise:</strong> {{ $details->type_of_premise }}</div>
                    <div class="col-sm-4 mb-3"><strong>Use Of Premise:</strong> {{ $details->use_of_premise }}</div>
                    <!-- <div class="col-sm-4 mb-3"><strong>Region:</strong> {{ $details->region }}</div>
                    <div class="col-sm-4 mb-3"><strong>Business Hub:</strong> {{ $details->business_hub }}</div>
                    <div class="col-sm-4 mb-3"><strong>Service Center:</strong> {{ $details->service_center }}</div>
                    <div class="col-sm-4 mb-3"><strong>DSS:</strong> {{ $details->dss }}</div> -->
                    <!-- <div class="col-sm-4 mb-3"><strong>Meter Book:</strong> {{ $details->meter_book }}</div> -->
                    <div class="col-sm-4 mb-3"><strong>Numbe of Accounts(s) Applied For:</strong> {{ $details->no_of_account_apply_for }}</div>

                    <div class="col-sm-4 mb-3"><strong>Means of Identification:</strong> {{ $details->uploadinformation?->means_of_identification  }}</div>

                    
                </div>
            </div>

            <div class="mb-4">
                <h6 class="text-muted mb-3">LandLoard Information</h6>
                <div class="row">
                    <div class="col-sm-4 mb-3"><strong>NIN:</strong> {{ $details->continuation?->nin_number }}</div>
                    <div class="col-sm-4 mb-3"><strong>Landlord Surname:</strong> {{ $details->continuation?->landlord_surname }}</div>
                    <div class="col-sm-4 mb-3"><strong>Landlord Othernames:</strong> {{ $details->continuation?->landlord_othernames }}</div>

                    @if (auth()->check() && auth()->user()->authority !== 'dtm')
                    <div class="col-sm-4 mb-3"><strong>Landlord Telephone:</strong> {{ $details->continuation?->landlord_telephone }}</div>
                    <div class="col-sm-4 mb-3"><strong>Landlord Email:</strong> {{ $details->continuation?->landlord_email }}</div>
                    @endif
                    <div class="col-sm-4 mb-3"><strong>Previous Employer:</strong> {{ $details->continuation?->name_address_of_previous_employer }}</div>
                    <div class="col-sm-4 mb-3"><strong>Previous Account Numbers:</strong> {{ $details->continuation?->previous_account_number }}</div>
                    <div class="col-sm-4 mb-3"><strong>Previous Meter Numbers:</strong> {{ $details->continuation?->previous_meter_number }}</div>

                    <div class="col-sm-4 mb-3"><strong>Preferred Method of Reciveing Bills:</strong> {{ $details->continuation?->prefered_method_recieving_bill }}</div>
                    
                    <div class="col-sm-4 mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-info text-dark">{{ $details->status }}</span>
                    </div>


                    


                    
                </div>
            </div>

            <!-- <div class="mb-4">
                <h6 class="text-muted mb-3">Geo Code Current Location</h6>
                <div class="row">
                    <div class="col-sm-6 mb-2"><strong>Longitude:</strong> {{ $details->continuation?->longitude }}</div>
                    <div class="col-sm-6 mb-2"><strong>Latitude:</strong> {{ $details->continuation?->latitude }}</div>
                </div>
            </div> -->


            <div class="mb-4">
                <h6 class="text-muted mb-3">No of Accounts</h6>
                <div class="row">
                  
                 @if($details->uploadedPictures && $details->uploadedPictures->count() > 0)
                    <table class="table table-bordered table-responsive">
                        <thead>
                            <tr>
                                <th>ID</th>
                                 <th>Region</th>
                                 <th>House No</th>
                                <th>Full Address</th>
                             <th>Business Hub</th>
                               <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Account No</th>
                                <th>Status</th>
                                <th>View</th>
                                 @canany(['billing', 'super_admin'])
                                        @if ($details->status == 'with-bhm')
                                        <!-- <button wire:click="generateAccount({{ $details->id }})" class="btn btn-md btn-success">Generate Accounts</button> -->
                                         <th>Generate Account</th>
                                        @endif
                                     @endcanany
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details->uploadedPictures as $account)
                                <tr>
                                    <td>{{ $account->id }}</td>
                                    <td>{{ $account->region }}</td>
                                    <td>{{ $account->house_no }}</td>
                                    <td>{{ $account->full_address }}</td>
                                    <td>{{ $account->business_hub }}</td>
                                    <td>{{ $account->latitude }}</td>
                                    <td>{{ $account->longitude }}</td>
                                    <td>{{ $account->account_no }}</td>
                                    <td>{{ $account->status == 0 ? 'Pending' : 'Completed' }}</td>
                                    <td> <a target="_blank" href="/tracking_details/{{$account->id }}/{{$account->tracking_id}}" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a></td>
                                    @canany(['billing', 'super_admin'])
                                        @if ($details->status == 'with-billing' && $account->status == 1 )
                                         <th><button wire:click="generateAccount( {{ $details->id }}, {{ $account->id }} )" class="btn btn-xs btn-primary">Generate Accounts</button></th>
                                        @endif
                                     @endcanany
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No accounts found.</p>
                @endif

                    
                </div>
            </div>

            <?php
               //echo  auth()->user()->authority . ' '. $details->id
            ?>


                                 @if(session()->has('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                <div class="d-flex gap-2">
                                     @canany(['super_admin', 'dtm'])
                                        @if ($details->status == 'with-dtm')
                                        <button wire:click="processforbhm({{ $details->id }})" class="btn btn-md btn-primary">Account(s) Validated</button>
                                        @endif
                                     @endcanany

                                      @canany(['super_admin', 'bhm'])
                                        @if ($details->status == 'with-bhm')
                                        <button wire:click="approveforbilling({{ $details->id }})" class="btn btn-md btn-success">Approve Accounts</button>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <button wire:click="rejectbacktodtm({{ $details->id }})" class="btn btn-md btn-danger">Reject</button>
                                        @endif
                                     @endcanany

                                      @canany(['super_admin', 'billing'])
                                        @if ($details->status == 'with-billing')
                                        <!-- <button wire:click="generateAccount({{ $details->id }}, {{ $account->id  }})" class="btn btn-md btn-success">Generate Multiple Accounts</button> -->
                                        @endif
                                     @endcanany
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Image or Additional Info -->
                    <div class="col-md-4">
                        <div class="card shadow-sm rounded-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Image Information</h6>
                            </div>
                            <div class="card-body text-center">  
                                 <p><strong>Customer Photo</p>
                                <img src="https://ipay.ibedc.com:7642/storage/{{ $details->uploadinformation?->photo }}" class="img-fluid rounded mb-3 w-50" alt="Customer Image">
                            </div>

                             <div class="card-body text-center">  
                                 <p><strong>Customer Means of Identification</p>
                                <img src="https://ipay.ibedc.com:7642/storage/{{ $details->uploadinformation?->identification }}" class="img-fluid rounded mb-3 w-50" alt="Customer Image">
                            </div>

                            <div class="card-body text-center">  
                                 <p><strong>Landlord Photo</p>
                                <!-- <img src="https://ipay.ibedc.com:7642/storage/{{ $details->continuation?->landlord_picture }}" class="img-fluid rounded mb-3 w-50 " alt="Customer Image"> -->
                                <img src="{{ asset('lun_pictures/' . basename($details->continuation?->landloard_picture)) }}" class="img-fluid rounded mb-3 w-50 " alt="Customer Image">
                               
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <x-footer />
        </div>
    </div>
</div>
