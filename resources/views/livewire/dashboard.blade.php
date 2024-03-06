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
                            <h4 class="card-title">Today's Transactions</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown1" x-placement="left-start">
                                <a class="dropdown-item" href="#">Started</a>
                                <a class="dropdown-item" href="#">Pending</a>
                                <a class="dropdown-item" href="#">Processing</a>
                                <a class="dropdown-item" href="#">Successful</a>
                              </div>
                            </div>
                          </div>
                          <div id="sales" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3"> {{ $count_transactions }}</h2>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small"><?= date('Y-m-d')?> <span class=" font-weight-normal">&nbsp;</span></p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                <i class="mdi mdi-calendar mr-1"></i>
                                <span class="text-left">
                                Oct
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
                            <h4 class="card-title">Sales</h4>
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
                                  <h2 class="mr-3">₦ {{ number_format($transactions, 2) }}</h2>
                                  <h3 class="text-success">+2.3%</h3>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold  text-small">Today's <span class=" font-weight-normal">(Sales)</span></p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                <i class="mdi mdi-calendar mr-1"></i>
                                <span class="text-left">
                                Oct
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
                            <h4 class="card-title">Users</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown3" x-placement="left-start">
                                <a class="dropdown-item" href="#">All Users</a>
                              </div>
                            </div>
                          </div>
                          <div id="returns" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3">10,000</h2>
                                  <h3 class="text-danger">+2.3%</h3>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small">All <span class=" font-weight-normal">(users)</span></p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                <i class="mdi mdi-calendar mr-1"></i>
                                <span class="text-left">
                                Oct
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
                            <h4 class="card-title">Complaint</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown4" x-placement="left-start">
                                <a class="dropdown-item" href="#">All Complaint</a>
                              </div>
                            </div>
                          </div>
                          <div id="marketing" class="carousel slide dashboard-widget-carousel position-static pt-2" data-ride="carousel">
                            <div class="carousel-inner">
                              <div class="carousel-item active">
                                <div class="d-flex flex-wrap align-items-baseline">
                                  <h2 class="mr-3">10,200</h2>
                                  <h3 class="text-success">+2.3%</h3>
                                </div>
                                <div class="mb-3">
                                  <p class="text-muted font-weight-bold text-small">Today's  <span class=" font-weight-normal">(complain)</span></p>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm btn-icon-text d-flex align-items-center">
                                <i class="mdi mdi-calendar mr-1"></i>
                                <span class="text-left">
                                Oct
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
                    <div class="col-12 col-lg-4 col-xl-4 grid-margin stretch-card">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">To do</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown10" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown10" x-placement="left-start">
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <a class="dropdown-item" href="#">Something else here</a>
                              </div>
                            </div>
                          </div>
                          <div class="add-items d-flex">
                            <input type="text" class="form-control todo-list-input" placeholder="Add list here">
                            <button class="btn btn-primary  todo-list-add-btn">Add to list</button>
                          </div>
                          <div class="list-wrapper">
                            <p class="text-muted">People who have a ticket reservation of the event is automatically mark as interested.</p>
                            <ul class="d-flex flex-column-reverse todo-list">
                              <li>
                                <div class="form-check">
                                  <label class="form-check-label text-muted font-weight-medium">
                                  <input class="checkbox" type="checkbox">Need to complete the product
                                  Manager needs.
                                  <i class="input-helper"></i></label>
                                </div>
                                <i class="remove mdi mdi-delete"></i>
                              </li>
                              <li>
                                <div class="form-check">
                                  <label class="form-check-label text-muted font-weight-medium">
                                  <input class="checkbox" type="checkbox">
                                  Buy Pizza on the way to work on web design
                                  <i class="input-helper"></i></label>
                                </div>
                                <i class="remove mdi mdi-delete"></i>
                              </li>
                              <li>
                                <div class="form-check">
                                  <label class="form-check-label text-muted font-weight-medium">
                                  <input class="checkbox" type="checkbox">
                                  Upload the draft design for admin dashboard
                                  <i class="input-helper"></i></label>
                                </div>
                                <i class="remove mdi mdi-delete"></i>
                              </li>
                              <li class="completed">
                                <div class="form-check">
                                  <label class="form-check-label text-muted font-weight-medium">
                                  <input class="checkbox" type="checkbox" checked="">
                                  This morning,be sure to get up early to eat breakfast!
                                  <i class="input-helper"></i></label>
                                </div>
                                <i class="remove mdi mdi-delete"></i>
                              </li>
                              <li>
                                <div class="form-check">
                                  <label class="form-check-label text-muted font-weight-medium">
                                  <input class="checkbox" type="checkbox">
                                  Accompany her to thr theater to see the musical.
                                  <i class="input-helper"></i></label>
                                </div>
                                <i class="remove mdi mdi-delete"></i>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 col-lg-8 col-xl-8 grid-margin stretch-card">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Sales</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown11" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown11" x-placement="left-start">
                                <a class="dropdown-item" href="#">Successful</a>
                              </div>
                            </div>
                          </div>
                          <p class="text-muted">People who have a ticket reservation of the event is automatically mark as interested.</p>
                          <div class="border pt-2 pb-2 mt-4 mb-3 border-radius-widget">
                            <ul class="d-md-flex flex-wrap align-items-baseline justify-content-center list-unstyled text-center mb-0 sales-legend">
                              <li class="border-right-sm">
                                <h6 class="font-weight-normal">Today's Sale</h6>
                                <h2 class="text-primary">2584</h2>
                                <p class="text-primary pl-md-4 pr-md-4">56.04 % Total</p>
                              </li>
                              <li class="border-right-sm">
                                <h6 class="font-weight-normal">This Months </h6>
                                <h2 class="text-primary pl-md-3 pr-3">46360</h2>
                                <p class="text-primary pl-3 pr-3">32.68 % Total</p>
                              </li>
                              <li class="border-right-sm">
                                <h6 class="font-weight-normal">This Year</h6>
                                <h2 class="text-primary">46360</h2>
                                <p class="text-primary">97.32% Total</p>
                              </li>
                              <li class="pb-2 pt-2 pl-4 pr-4">
                                <h6 class="font-weight-normal">All Sales</h6>
                                <h2 class="text-primary">93819</h2>
                                <p class="text-primary">76.47% Total</p>
                              </li>
                            </ul>
                          </div>
                          <div class="row mt-1 d-sm-flex">
                            <div class="col-12">
                              <canvas id="salesChart"></canvas>
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
                            <h4 class="card-title">Latest Transactions</h4>
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
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Transaction ID</th>
                                  <th>Account No</th>
                                  <th>Meter No</th>
                                  <th>Customer Name</th>
                                  <th>Email</th>
                                  <th>Phone</th>
                                  <th>Acount Type</th>
                                  <th>Business Hub</th>
                                  <th>Status</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td>188928893889 </td>
                                  <td>11/43/12/0942-01</td>
                                  <td> <div class="text-dark font-weight-medium">6547-3DESC9835</div> </td>
                                  <td>fortune@gmail.com</td>
                                  <td>09083904993</td>
                                  <td>Nike Hazard</td>
                                  <td>Prepaid</td>
                                  <td>Ijeun</td>
                                  <td><label class="badge badge-success">Completed</label></td>
                                  <td>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-grease-pencil"></i></a>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-delete"></i></a>
                                  </td>
                                </tr>

                                <tr>
                                  <td>188928893889 </td>
                                  <td>11/43/12/0942-01</td>
                                  <td> <div class="text-dark font-weight-medium">6547-3DESC9835</div> </td>
                                  <td>fortune@gmail.com</td>
                                  <td>09083904993</td>
                                  <td>Nike Hazard</td>
                                  <td>Prepaid</td>
                                  <td>Ijeun</td>
                                  <td><label class="badge badge-success">Completed</label></td>
                                  <td>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-grease-pencil"></i></a>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-delete"></i></a>
                                  </td>
                                </tr>
                                
                                <tr>
                                  <td>188928893889 </td>
                                  <td>11/43/12/0942-01</td>
                                  <td> <div class="text-dark font-weight-medium">6547-3DESC9835</div> </td>
                                  <td>fortune@gmail.com</td>
                                  <td>09083904993</td>
                                  <td>Nike Hazard</td>
                                  <td>Prepaid</td>
                                  <td>Ijeun</td>
                                  <td><label class="badge badge-success">Completed</label></td>
                                  <td>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-grease-pencil"></i></a>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-delete"></i></a>
                                  </td>
                                </tr>

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
