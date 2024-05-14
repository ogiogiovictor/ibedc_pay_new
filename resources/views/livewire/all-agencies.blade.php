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
                            <h4 class="card-title">Total Agencies</h4>
                            
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3" wire:poll> {{ $agencyCount }}</h2>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small">All agencies<span class=" font-weight-normal">&nbsp;</span></p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                <i class="mdi mdi-calendar mr-1"></i>
                                <span class="text-left">
                                <!-- Oct -->
                                </span>
                                </button>
                              </div>
                              
                            </div>
                            <a class="carousel-control-prev" href="#sales" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#sales" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                            </a>
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
                                  <th>Agency Code</th>
                                  <th>Agency Name</th>
                                  <th>Email</th>
                                  <th>Number of Agents</th>
                                  <th>Status</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>


                              
                              @if(collect($agencies)->isNotEmpty())

                              @foreach($agencies as $agency)
                                <tr>
                                  <td>{{ $agency->created_at->format('Y-m-d') }} </td>
                                  <td>{{ $agency->agent_code }} </td>
                                  <td>{{ $agency->agent_name }}</td>
                                  <td>{{ $agency->agent_email }}</td>
                                  <td> <div class="text-dark font-weight-medium">{{ $agency->no_of_agents }}</div> </td>
                                 
                                  <td>
                                    @if($agency->status == "1")
                                    <label class="badge badge-success">Active</label>
                                    @else
                                    <label class="badge badge-danger">Inactive</label>
                                    @endif
                                  
                                  </td>
                                  <td>
                                    <a href="/view_transactions/{{ $agency->id }}" wire:navigate class="mr-1 p-2 btn btn-xs btn-secondary">View</a>
                                    &nbsp;
                                    @can('super_admin')
                                     <a href="/add_target/{{ $agency->id }}" wure:navigate class="mr-1 p-2 btn btn-xs btn-danger">Add Target</a>  &nbsp;
                                    @endcan
                                  </td>
                                </tr>

                                @endforeach
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Agency Found</td>
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

