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
                            <h4 class="card-title">Total Agency Collection</h4>
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3" wire:poll>₦ {{  $this->totalCollection}}</h2>
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
                           
                          </div>
                        </div>
                      </div>
                    </div>


                    <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Monthly Collection</h4>
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3" wire:poll>₦ {{ $monthlyCollection }}</h2>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small"><?php echo date('M'); ?><span class=" font-weight-normal">&nbsp;</span></p>
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
                            <h4 class="card-title">Daily Collection</h4>
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3" wire:poll>₦{{ $todaysCollection ?: 0 }}</h2>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small"><?php echo date('Y-m-d'); ?><span class=" font-weight-normal">&nbsp;</span></p>
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
                            <h4 class="card-title">Pending Collections</h4>
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3" wire:poll>₦ {{ $pendingCollection ?: 0}}</h2>
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
                            <h4 class="card-title">Transactions for {{ \App\Models\Agency\Agents::where("id", $id)->value("agent_name") }}</h4>
                          </div>
                          <div class="table-responsive"  wire:poll>
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Date</th>
                                  <th>Transaction ID</th>
                                  <th>Account No</th>
                                  <th>Meter No</th>
                                  <th>Customer Name</th>
                                  <th>Amount</th>
                                  <th>Agency</th>
                                  <th>User</th>
                                  <th>Status</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>


                             
                              @if(collect($transactions)->isNotEmpty())

                              @foreach($transactions['data'] as $tx)
                                <tr>
                                  <td>{{ \Carbon\Carbon::parse($tx['created_at'])->format('Y-m-d')}} </td>
                                  <td>{{ $tx['transaction_id'] }} </td>
                                  <td>{{ $tx['account_number'] }}</td>
                                  <td>{{ $tx['meter_no'] }}</td>
                                  <td>{{ $tx['customer_name'] }}</td>
                                  <td>₦{{ $tx['amount'] }}</td>
                                  <td>{{ $tx['agency'] }}</td>
                                  <td>{{ \App\Models\User::where("id", $tx['user_id'])->value("name") }}</td>
                                  <td>
                                    @if($tx['status'] == "success")
                                    <label class="badge badge-success">{{ $tx['status'] }}</label>
                                    @elseif($tx['status'] == "started")
                                    <label class="badge badge-secondary">{{ $tx['status'] }}</label>
                                    @else
                                    <label class="badge badge-warning">{{ $tx['status'] }}</label>
                                    @endif
                                  
                                  </td>
                                  <td>
                                    <a href="" wire:navigate class="mr-1 p-2 btn btn-xs btn-secondary">View</a>
                                   
                                  </td>
                                </tr>

                                @endforeach

                                <nav>
                                    <ul class="pagination">
                                        @foreach($transactions['links'] as $link)
                                            <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                                                <!-- <a class="page-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a> -->
                                                <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </nav>
                               

                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Transaction Found</td>
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

