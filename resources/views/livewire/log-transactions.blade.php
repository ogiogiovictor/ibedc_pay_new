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
                            <h4 class="card-title">Total IBEDCPay Collections</h4>
                            
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3" wire:poll>₦ {{ number_format($totalCollection, 2) }}</h2>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small">Total Collection<span class=" font-weight-normal">&nbsp;</span></p>
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

                    <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Monthly Collection</h4>
                           
                          </div>
                          <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3 text-success" wire:poll>₦ {{ number_format($monthlyCollection, 2) }}</h2>
                                  <!-- <h3 class="text-success">+2.3%</h3> -->
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold  text-small"><?php echo date('F'); ?> Collection  <span class=" font-weight-normal"></span></p>
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

                    <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Today's Collection</h4>
                           
                          </div>
                          <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3 text-success" wire:poll>₦ {{ number_format($today, 2) }}</h2>
                                  <!-- <h3 class="text-success">+2.3%</h3> -->
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold  text-small">Collection for <span class=" font-weight-normal"><?php echo date('Y-m-d'); ?></span></p>
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
                            <h4 class="card-title">Pending Transactions - IBEDCPay v1</h4>
                              <form class="form-inline justify-content-end" wire:submit.prevent="searchTransactions">
                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="meter_no">Meter No</option>
                                  <option value="account_number">Account Number</option>
                                  <option value="customer_name">Customer Name</option>
                                  <option value="transaction_id">Transaction ID</option>
                                  <option value="email">Email</option>
                                  <option value="amount">Amount</option>
                                  <option value="BUID">Business Hub</option>
                                  <option value="providerRef">Provider Reference</option>
                                </select>
                              </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">Enter Value:</label>
                                <input type="text" class="form-control" wire:model="clearValue" id="inputField" placeholder="Enter value">
                              </div>
                              <button type="submit" class="btn btn-md btn-primary">Search</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                              <button type="submit" class="btn btn-md btn-secondary" wire:click="exportTransactions">Export</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                              @if (session()->has('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                              @endif
                            </form>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown12" x-placement="left-start">
                                <a class="dropdown-item" href="#">Pending</a>
                                <a class="dropdown-item" href="#">Processing</a>
                                <a class="dropdown-item" href="#">Successful</a>
                              </div>
                            </div>
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
                                  <th>Email</th>
                                  <th>Amount</th>
                                  <th>Acount Type</th>
                                  <!-- <th>Business Hub</th> -->
                                  <th>Status</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                              
                              @if(collect($transactions)->isNotEmpty())

                              @foreach($transactions as $transaction)
                                <tr>
                                  <td>{{ $transaction->created_at->format('Y-m-d') }} </td>
                                  <td>{{ $transaction->transaction_id }} </td>
                                  <td>{{ $transaction->account_number }}</td>
                                  <td>{{ $transaction->meter_no }}</td>
                                  <td> <div class="text-dark font-weight-medium">{{ $transaction->customer_name }}</div> </td>
                                  <td>{{ $transaction->email }}</td>
                                  <td>₦{{ number_format($transaction->amount, 2) }}</td>
                                  <td>{{ $transaction->account_type }}</td>
                                  <!-- <td>{{ $transaction->BUID }}</td> -->
                                  <td>
                                    @if($transaction->status == "started")
                                    <label class="badge badge-default">Started</label>
                                    @elseif($transaction->status == "processing")
                                    <label class="badge badge-warning">Processing</label>
                                    @elseif($transaction->status == "pending")
                                    <label class="badge badge-warning">Pending</label>
                                    @elseif($transaction->status == "success")
                                    <label class="badge badge-success">Successful</label>
                                    @else
                                    <label class="badge badge-danger">Failed</label>
                                    @endif
                                  
                                  </td>
                                  <td>
                                    <a href="/view_transactions/{{ $transaction->transaction_id }}" wire:navigate class="mr-1 p-2 btn btn-xs btn-primary">View</a>
                                    &nbsp;
                                    @can('super_admin')
                                    <!-- <a href="#" class="mr-1 p-2 btn btn-xs btn-danger">Retry</a> &nbsp;&nbsp; -->
                                    <a href="#" wire:navigate class="mr-1 p-2 btn btn-xs btn-danger">Edit</a>
                                    @endcan
                                  </td>
                                </tr>

                                @endforeach
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

 <!-- JavaScript for real-time updates -->
 <script>
        document.addEventListener('livewire:load', function () {
            setInterval(function () {
                @this.call('loadData');
            }, 30000); // Refresh data every 30 seconds (adjust as needed)
        });
</script>
