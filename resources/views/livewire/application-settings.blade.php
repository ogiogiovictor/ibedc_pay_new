<div>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


                  
                    <x-topbar />

                 
            <div class="tab-content tab-transparent-content pb-0">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                 
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Application Settings</h4>
                            
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline"><br/><br/>
                                  <!-- <h2 class="mr-3" wire:poll> </h2> -->
                                  @can('super_admin')
                                    <button wire:click="runPaymentLookUp()" class="btn btn-xs btn-danger">Run PaymentLookUp</button>
                                  @endcan
                                </div>
                               
                              </div>
                              
                            </div>
                           
                            
                          </div>
                        </div>
                      </div>
                    </div>

                 

                  </div>





                  <div class="row">
                    <div class="col-12 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Agencies</h4>
                          </div>
                          <div class="table-responsive"  wire:poll>
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Date</th>
                                  <th>Service Name</th>
                                  <th>Status</th>
                                  <th>Description</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session()->has('success'))
                            <div class="alert alert-danger">
                                {{ session('success') }}
                            </div>
                        @endif

                              
                              @if(collect($settings)->isNotEmpty())

                              @foreach($settings as $set)
                                <tr>
                                  <td>{{ $set->id }} </td>
                                  <td>{{ $set->service_name }} </td>
                                  <td>
                                    @if($set->status == "on")
                                    <label class="badge badge-success">Active</label>
                                    @else
                                    <label class="badge badge-danger">Inactive</label>
                                    @endif
                                  
                                  </td>
                                  <td>{{ $set->description }}</td>
                                  <td>
                                    
                                    @can('super_admin')
                                    @if($set->status == "on")
                                    <button wire:click="toggleService({{ $set->id }})" class="btn btn-xs btn-danger">Deactivate Service</button>
                                    <!-- <button class="btn btn-danger">Deactivate Service</button> -->
                                    @else
                                    <button wire:click="toggleService({{ $set->id }})" class="btn btn-xs btn-success">Activate Service</button>
                                    <!-- <button class="btn btn-danger">Deactivate Service</button> -->
                                    @endif
                                    @endcan
                                  </td>
                                </tr>

                                @endforeach
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No setting found Found</td>
                                </tr>
                                @endif

                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                 
                </div>
              
              </div>
                    
                                    

                    </div>
                </div>
             </div>

        <x-footer />

        </div>

    </div>

</div>

