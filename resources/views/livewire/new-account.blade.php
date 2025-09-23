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
                            <h4 class="card-title">Total Customers</h4>
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
                                  <h2 class="mr-3 text-success">{{  $totalCustomers }}</h2>
                                  <!-- <h3 class="text-success">+2.3%</h3> -->
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold  text-small">Monthly <span class=" font-weight-normal">({{  $totalCustomers }})</span></p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                <i class="mdi mdi-calendar mr-1"></i>
                                <span class="text-left">
                                <!-- Oct -->
                                </span>
                                </button>
                              </div>
                            
                            </div>
                            <a class="carousel-control-prev" href="#purchases" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#purchases" role="button" data-slide="next">
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
                            <h4 class="card-title">All Customers<strong><span style="color:red"> </span></strong></h4>
                            <form class="form-inline justify-content-end" wire:submit.prevent="searchTransactions">
                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="meter_no">Tracking ID</option>
                                  <option value="account_number">Surname</option>
                                </select>
                              </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">Enter Value:</label>
                                <input type="text" class="form-control" wire:model="clearValue" id="inputField" placeholder="Enter value">
                              </div>
                              <button type="submit" class="btn btn-md btn-primary">Search</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                              <!-- <button type="submit" class="btn btn-md btn-secondary" wire:click="exportTransactions">Export</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; -->
                              @if (session()->has('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                              @endif
                            </form>
                            
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Applied Date</th>
                                  <th>Tracking Number</th>
                                  <th>Surname</th>
                                  <th>Firstname</th>
                                  <th>Other Names</th>
                                  <th>Region</th>
                                  <!-- <th>Number of Accounts</th> -->
                                  <!-- <th>Status</th> -->
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                            
                            
                              @if(count($customers['links']) > 0)

                              @foreach($customers['data'] as $transaction)
                                <tr>
                                  <!-- <td> {{ $transaction['created_at'] }} </td> -->
                                  <td>{{ \Carbon\Carbon::parse($transaction['created_at'])->timezone('Africa/Lagos')->format('d M Y h:i A') }}</td>

                                  <td><strong>{{ $transaction['tracking_id'] }}</strong></td>
                                  <td>{{ $transaction['surname'] }} </td>
                                  <td>{{ $transaction['firstname'] }} </td>
                                  <td>{{ $transaction['other_name'] }} </td>
                                  <td>{{ $transaction['region'] }}</td>
                                 
                                 
                                
                                  
                                  <td>
                                    <!-- <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a> -->
                                    <a href="account_details/{{ $transaction['tracking_id'] }}" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                  </td>
                                </tr>

                                @endforeach

                                <nav>
                                    <ul class="pagination">
                                        @foreach($customers['links'] as $link)
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
