<div wire:poll>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


                  
                    <!-- <x-topbar /> -->
                <h4>Transaction Details FOR {{ $transactions->customer_name }}    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                     <a href="/log_transactions" wire:navigation> << Back</a></h4>
                 
            <div class="tab-content tab-transparent-content pb-0">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                 

                  <div class="row">
                    <div class="col-8 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h6 class="card-title">Transaction Details</h6><hr/>
                          
                          </div>
                          <div class="table-responsive">
                            
                          <ul class="bullet-line-list">
										<li>
											<h6>Transaction Date</h6>
											<p>{{ $transactions->created_at }} </p>
											<p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Transaction ID</h6>
											<p>{{ $transactions->id }} </p>
											<p class="text-muted mb-4">
												<i class="mdi mdi-clock-outline"></i>
											</p>
										</li>

                                        <li>
											<h6>Phone</h6>
											<p>{{ $transactions->phone }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Amount </h6>
											<p>{{ $transactions->amount }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Account Type </h6>
											<p>{{ $transactions->account_type }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Account Number </h6>
											<p>{{ $transactions->account_numer }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Provider </h6>
											<p>{{ $transactions->provider }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Status </h6>
											<p class="info">{{ $transactions->status }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>provider Reference </h6>
											<p class="success">{{ $transactions->providerRef }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Receipt No </h6>
											<p>{{ $transactions->receiptno }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Business Hub </h6>
											<p>{{ $transactions->BUID }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Payment Source </h6>
											<p>{{ $transactions->payment_source }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Owner </h6>
											<p>{{ $transactions->owner }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Latitude </h6>
											<p>{{ $transactions->latitude }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Longitude </h6>
											<p>{{ $transactions->longitude }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                       
										
									</ul>

                                    @can('super_admin')
                                        @if (!$transactions->receiptno)
                                            <button wire:click="processTransaction({{ $transactions->id }})" class="btn btn-xs btn-danger">Resync</button>
                                        @endif
                                    @endcan

                                 

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
