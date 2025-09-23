<div wire:poll>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


<!--                   
                    <x-topbar /> -->

                 
            <div class="tab-content tab-transparent-content pb-0">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">

                  <div class="row">
                    



                        <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                          <div class="card">
                            <div class="card-body">
                              <div class="d-flex flex-wrap justify-content-between">
                                <h4 class="card-title">Started</h4>
                                <div class="dropdown dropleft card-menu-dropdown">
                                  <button class="btn p-0" type="button" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                                  </button>
                                </div>
                              </div>
                              <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                                <div class="carousel-inner">
                                  <div class="carousel-item active">
                                    <div class="d-flex flex-wrap align-items-baseline">
                                      <h2 class="mr-3 text-info">{{ $started ?? 0 }}</h2>
                                      <!-- <h3 class="text-success">+2.3%</h3> -->
                                    </div>
                                    <div class="mb-3">
                                      <p class="text-muted font-weight-bold  text-small"><span class=" font-weight-normal"><?php echo date('Y-m-d'); ?></span></p>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                    <i class="mdi mdi-calendar mr-1"></i>
                                    <span class="text-left">
                                    <!-- Oct -->
                                    </span>
                                    </button>
                                  </div>
                                
                                </div>
                              
                              
                              </div>
                            </div>
                          </div>
                        </div>


                        
                      <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                          <div class="card">
                            <div class="card-body">
                              <div class="d-flex flex-wrap justify-content-between">
                                <h4 class="card-title">With DTM</h4>
                                <div class="dropdown dropleft card-menu-dropdown">
                                  <button class="btn p-0" type="button" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                                  </button>
                                </div>
                              </div>
                              <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                                <div class="carousel-inner">
                                  <div class="carousel-item active">
                                    <div class="d-flex flex-wrap align-items-baseline">
                                      <h2 class="mr-3 text-warning">{{ $pending }}</h2>
                                      <!-- <h3 class="text-success">+2.3%</h3> -->
                                    </div>
                                    <div class="mb-3">
                                      <p class="text-muted font-weight-bold  text-small"><span class=" font-weight-normal"><?php echo date('Y-m-d'); ?></span></p>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                    <i class="mdi mdi-calendar mr-1"></i>
                                    <span class="text-left">
                                    <!-- Oct -->
                                    </span>
                                    </button>
                                  </div>
                                
                                </div>
                              
                              
                              </div>
                            </div>
                          </div>
                        </div>


                         <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                          <div class="card">
                            <div class="card-body">
                              <div class="d-flex flex-wrap justify-content-between">
                                <h4 class="card-title">Compliance</h4>
                                <div class="dropdown dropleft card-menu-dropdown">
                                  <button class="btn p-0" type="button" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                                  </button>
                                </div>
                              </div>
                              <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                                <div class="carousel-inner">
                                  <div class="carousel-item active">
                                    <div class="d-flex flex-wrap align-items-baseline">
                                      <h2 class="mr-3 text-processing">{{ $withcompliance ?? 0 }}</h2>
                                      <!-- <h3 class="text-success">+2.3%</h3> -->
                                    </div>
                                    <div class="mb-3">
                                      <p class="text-muted font-weight-bold  text-small"> <span class=" font-weight-normal"><?php echo date('Y-m-d'); ?></span></p>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                    <i class="mdi mdi-calendar mr-1"></i>
                                    <span class="text-left">
                                    <!-- Oct -->
                                    </span>
                                    </button>
                                  </div>
                                
                                </div>
                              
                              
                              </div>
                            </div>
                          </div>
                        </div>


                         <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                          <div class="card">
                            <div class="card-body">
                              <div class="d-flex flex-wrap justify-content-between">
                                <h4 class="card-title">With Billing</h4>
                                <div class="dropdown dropleft card-menu-dropdown">
                                  <button class="btn p-0" type="button" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                                  </button>
                                </div>
                              </div>
                              <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                                <div class="carousel-inner">
                                  <div class="carousel-item active">
                                    <div class="d-flex flex-wrap align-items-baseline">
                                      <h2 class="mr-3 text-warning">{{ $withbilling ?? 0 }}</h2>
                                      <!-- <h3 class="text-success">+2.3%</h3> -->
                                    </div>
                                    <div class="mb-3">
                                      <p class="text-muted font-weight-bold  text-small"><span class=" font-weight-normal"><?php echo date('Y-m-d'); ?></span></p>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                    <i class="mdi mdi-calendar mr-1"></i>
                                    <span class="text-left">
                                    <!-- Oct -->
                                    </span>
                                    </button>
                                  </div>
                                
                                </div>
                              
                              
                              </div>
                            </div>
                          </div>
                        </div>




                          <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                          <div class="card">
                            <div class="card-body">
                              <div class="d-flex flex-wrap justify-content-between">
                                <h4 class="card-title">Completed</h4>
                                <div class="dropdown dropleft card-menu-dropdown">
                                  <button class="btn p-0" type="button" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                                  </button>
                                </div>
                              </div>
                              <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                                <div class="carousel-inner">
                                  <div class="carousel-item active">
                                    <div class="d-flex flex-wrap align-items-baseline">
                                      <h2 class="mr-3 text-success">{{ $completed ?? 0 }}</h2>
                                      <!-- <h3 class="text-success">+2.3%</h3> -->
                                    </div>
                                    <div class="mb-3">
                                      <p class="text-muted font-weight-bold  text-small"> <span class=" font-weight-normal"><?php echo date('Y-m-d'); ?></span></p>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                    <i class="mdi mdi-calendar mr-1"></i>
                                    <span class="text-left">
                                    <!-- Oct -->
                                    </span>
                                    </button>
                                  </div>
                                
                                </div>
                              
                              
                              </div>
                            </div>
                          </div>
                        </div>


                        
                          <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                          <div class="card">
                            <div class="card-body">
                              <div class="d-flex flex-wrap justify-content-between">
                                <h4 class="card-title">Rejected</h4>
                                <div class="dropdown dropleft card-menu-dropdown">
                                  <button class="btn p-0" type="button" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                                  </button>
                                </div>
                              </div>
                              <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                                <div class="carousel-inner">
                                  <div class="carousel-item active">
                                    <div class="d-flex flex-wrap align-items-baseline">
                                      <h2 class="mr-3 text-danger">{{ $rejected ?? 0 }}</h2>
                                      <!-- <h3 class="text-success">+2.3%</h3> -->
                                    </div>
                                    <div class="mb-3">
                                      <p class="text-muted font-weight-bold  text-small"> <span class=" font-weight-normal"><?php echo date('Y-m-d'); ?></span></p>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                    <i class="mdi mdi-calendar mr-1"></i>
                                    <span class="text-left">
                                    <!-- Oct -->
                                    </span>
                                    </button>
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
                          <p>
                            <h4 class="card-title">SUMMARY DASHBOARD </h4><hr/>
                            </p>
                          <div class="d-flex flex-wrap justify-content-between">
                            
                            <form class="form-inline justify-content-end" wire:submit.prevent="searchTransactions">
                             
                            @if (session()->has('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">From:</label>
                                <input type="date" class="form-control" wire:model="fromdate" id="fromdate">
                              </div>
                                <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">To:</label>
                                <input type="date" class="form-control" wire:model="todate" id="todate">
                              </div>
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="tracking_id">Tracking ID</option>
                                  <option value="surname">Surname</option>
                                  <option value="status">Status</option>
                                </select>
                              </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">Enter Value:</label>
                                <input type="text" class="form-control" wire:model="clearValue" id="inputField" placeholder="Enter value">
                              </div>
                              <button type="submit" class="btn btn-md btn-primary" wire:submit.prevent="searchTransactions">Search</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                              <button type="button" class="btn btn-md btn-secondary" wire:click="exportTransactions">Export </button>
                              <!-- <button type="submit" class="btn btn-md btn-secondary" wire:click="exportTransactions">Export</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; -->
                            
                            </form>
                            
                          </div>
                          <hr/>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Applied Date</th>
                                  <th>Tracking Number</th>
                                  <th>Customer Name</th>
                                  <!-- <th>Latitude</th>
                                  <th>Longitude</th> -->
                                  <th>Region</th>
                                  <th>Business Hub</th>
                                  <th>Service Center</th>
                                  <th>Status</th>
                                  <th>Account No</th>
                                  <th>Date Past</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                            
                             
                              @if(count($accounts['links']) > 0)

                              @foreach($accounts['data'] as $transaction)
                                <tr>
                                  <!-- <td> {{ $transaction['created_at'] }} </td> -->
                                  <td>{{ \Carbon\Carbon::parse($transaction['created_at'])->timezone('Africa/Lagos')->format('Y-m-d H:i:s') }}</td>
                                  <td><strong>{{ $transaction['tracking_id'] }}</strong></td>
                                  <td> {{ $transaction['customer']['surname'] }}  {{ $transaction['customer']['firstname'] }}  {{ $transaction['customer']['other_name'] }} </td>
                                
                                  <td>{{ $transaction['region'] }}</td>
                                  <td>{{ $transaction['business_hub'] }}</td>
                                  <td>{{ $transaction['service_center'] }}</td>
                                  <td>
                                      @if($transaction['status'] == "0")
                                      <label class="badge badge-info">Started</label>
                                      @elseif($transaction['status'] == "1")
                                      <label class="badge badge-warning">with DTM</label>
                                      @elseif($transaction['status'] == "3")
                                      <label class="badge badge-warning">with Compliance</label>
                                      @elseif($transaction['status'] == "2")
                                      <label class="badge badge-warning">with Billing</label>
                                      @elseif($transaction['status'] == "4")
                                      <label class="badge badge-success">Completed</label>
                                       @elseif($transaction['status'] == "5")
                                      <label class="badge badge-danger">Rejected</label>
                                      @else
                                      <label class="badge badge-danger">N/A</label>
                                      @endif
                                  
                                  </td>
                                   <td>{{ $transaction['account_no'] }}</td>
                                  <td>
                                      {{ $transaction['status'] == '0'
                                          ? \Carbon\Carbon::parse($transaction['created_at'])->diffForHumans()
                                          : \Carbon\Carbon::parse($transaction['updated_at'])->diffForHumans() }}
                                  </td>
                                 
                                
                                  
                                  <td>
                                    <!-- <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a> -->
                                     @canany(['super_admin', 'dtm', 'billing', 'bhm', 'rico', 'audit'])
                                    <a href="account_details/{{ $transaction['tracking_id'] }}" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                     @endcanany

                                    @canany(['super_admin', 'mso'])
                                    <a href="{{ url('evaluation/' . $transaction['tracking_id']) }}" class="btn btn-primary btn-sm mr-1">
                                        TE
                                    </a>
                                @endcanany

                                  </td>
                                </tr>

                                @endforeach

                              
                                  <nav>
                                    <ul class="pagination">
                                        @foreach($accounts['links'] as $link)
                                            <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                                                <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </nav>


                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Customer Found</td>
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
