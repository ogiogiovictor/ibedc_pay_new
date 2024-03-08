<div wire:poll>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


                  
                    <!-- <x-topbar /> -->
                <h4>All Log Details For ID {{ $details->id }}</h4>
                 
            <div class="tab-content tab-transparent-content pb-0">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                 

                  <div class="row">
                    <div class="col-8 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h6 class="card-title">System Log Details</h6><hr/>
                          
                          </div>
                          <div class="table-responsive">
                            
                          <ul class="bullet-line-list">
										<li>
											<h6>Date Logged</h6>
											<p>{{ $details->created_at }} </p>
											<p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>User ID</h6>
											<p>{{ $details->user_id }} </p>
											<p class="text-muted mb-4">
												<i class="mdi mdi-clock-outline"></i>
											</p>
										</li>

                                        <li>
											<h6>IP Address</h6>
											<p>{{ $details->ip_address }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>URL </h6>
											<p>{{ $details->url }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Method </h6>
											<p>{{ $details->method }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>User Agent </h6>
											<p>{{ $details->user_agent }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Payload </h6>
											<p>{{ $details->payload }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Status Code </h6>
											<p class="btn btn-warning">{{ $details->status_code }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Response Code </h6>
                                            <p>{!! $details->response !!}</p>
                                            <p class="text-muted mb-4"></p>
										</li>
										
									</ul>

                                 

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
