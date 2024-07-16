<div wire:poll>
    
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
                            <h4 class="card-title">Total Agents</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                            </div>
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3">  1</h2>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small">Registered Today: <span class=" font-weight-bold"> 2</span></p>
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
                            <h4 class="card-title"><?php echo date('F') ?> Monthly Collection</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown2" x-placement="left-start">
                                <a class="dropdown-item" href="#">Processing</a>
                                <a class="dropdown-item" href="#">Successful</a>
                              </div>
                            </div>
                          </div>
                          <div id="purchases" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3 text-success">₦ 2000</h2>
                                  <!-- <h3 class="text-success">+2.3%</h3> -->
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold  text-small">Monthly <span class=" font-weight-normal">(Sales)</span></p>
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
                            <h4 class="card-title">Monthly Target</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown3" x-placement="left-start">
                                <a class="dropdown-item" href="#">Target</a>
                              </div>
                            </div>
                          </div>
                          <div id="returns" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3 text-danger">450</h2>
                                  <!-- <h3 class="text-danger">+2.3%</h3> -->
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small">
                                    <span class=" font-weight-normal"> <a href="complaints"> (view targets) </a>
                                    </span></span>
                                </p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                <i class="mdi mdi-calendar mr-1"></i>
                                <span class="text-left">
                                <!-- Oct -->
                                </span>
                                </button>
                              </div>
                              
                             
                            </div>
                            <a class="carousel-control-prev" href="#returns" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#returns" role="button" data-slide="next">
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
                            <h4 class="card-title">Transaction Count</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown4" x-placement="left-start">
                                <a class="dropdown-item" href="#">Today's Transactions</a>
                              </div>
                            </div>
                          </div>
                          <div id="marketing" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3 text-primary">3</h2>
                                  <!-- <h3 class="text-success">+2.3%</h3> -->
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small">&nbsp;<span class=" font-weight-normal">
                                    <a href="complaintsi"> (view transactions) </a>
                                  </span>
                                  </p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                <i class="mdi mdi-calendar mr-1"></i>
                                <span class="text-left">
                                <!-- Oct -->
                                </span>
                                </button>
                              </div>
                              
                             
                            </div>
                            <a class="carousel-control-prev" href="#marketing" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#marketing" role="button" data-slide="next">
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
                            <h4 class="card-title">Latest Transactions</h4>
                            <form class="form-inline justify-content-end" wire:submit.prevent="searchTransactions">
                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="meter_no">Meter No</option>
                                  <option value="account_number">Account Number</option>
                                  <option value="customer_name">Customer Name</option>
                                  <option value="transaction_id">Transaction ID</option>
                                  <option value="agent">Agent</option>
                                  <option value="amount">Amount</option>
                                  <option value="BUID">Business Hub</option>
                                  <option value="providerRef">Provider Reference</option>
                                  <option value="status">Status</option>
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
                                  <th>Date</th>
                                  <th>Transaction ID</th>
                                  <th>Account No</th>
                                  <th>Meter No</th>
                                  <th>Customer Name</th>
                                  <th>Agent</th>
                                  <th>Amount</th>
                                  <th>Acount Type</th>
                                  <th>Business Hub</th>
                                  <th>Status</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                              Table goes here
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
