<div wire:poll>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


                  
                    <!-- <x-topbar /> -->
                <h4>Transaction Details FOR {{ $all_transactions->customer_name }}    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                     <a href="/transactions" wire:navigation> << Back</a></h4>
                 
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
											<p>{{ $all_transactions->created_at }} </p>
											<p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Date Token Sent</h6>
											<p>{{ $all_transactions->updated_at }} </p>
											<p class="text-muted mb-4"></p>
										</li>


                                        <li>
											<h6>ID</h6>
											<p>{{ $all_transactions->id }} </p>
											<p class="text-muted mb-4">
												<i class="mdi mdi-clock-outline"></i>
											</p>
										</li>

                                        <li>
											<h6>Transaction ID</h6>
											<p>{{ $all_transactions->transaction_id }} </p>
											<p class="text-muted mb-4">
												<i class="mdi mdi-clock-outline"></i>
											</p>
										</li>

                                        <li>
											<h6>Phone</h6>
											<p>{{ $all_transactions->phone }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Email</h6>
											<p>{{ $all_transactions->email }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Amount </h6>
											<p>{{ number_format($all_transactions->amount, 2) }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Account Type </h6>
											<p>{{ $all_transactions->account_type }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Account Number </h6>
											<p>{{ $all_transactions->account_numer }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Meter Number </h6>
											<p>{{ $all_transactions->meter_no }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Provider </h6>
											<p>{{ $all_transactions->provider }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Status </h6>
											<p class="info">{{ $all_transactions->status }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Provider Reference </h6>
											<p class="success">{{ $all_transactions->providerRef }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Receipt No </h6>
											<p>{{ $all_transactions->receiptno }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Business Hub </h6>
											<p>{{ $all_transactions->BUID }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Payment Source </h6>
											<p>{{ $all_transactions->payment_source }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Owner </h6>
											<p>{{ $all_transactions->owner }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>


                                        <li>
											<h6>Description </h6>
											<p>{{ $all_transactions->Descript }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Source Type </h6>
											<p>{{ $all_transactions->source_type }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Units </h6>
											<p>{{ $all_transactions->units }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Cost Of Units </h6>
											<p>{{ $all_transactions->costOfUnits }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>


                                        <li>
											<h6>VAT </h6>
											<p>{{ $all_transactions->VAT }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Agency </h6>
											<p>{{ $all_transactions->agency }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Minimum Purchase </h6>
											<p>{{ $all_transactions->minimumPurchase }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Tariff Code </h6>
											<p>{{ $all_transactions->tariffcode }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>


                                        <li>
											<h6>Customer Arrears </h6>
											<p>{{ $all_transactions->customerArrears }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Tariff </h6>
											<p>{{ $all_transactions->tariff }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>


                                        <li>
											<h6>Service Band </h6>
											<p>{{ $all_transactions->serviceBand }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Feeder Name </h6>
											<p>{{ $all_transactions->feederName }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Distribution Station </h6>
											<p>{{ $all_transactions->dssName }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Latitude </h6>
											<p>{{ $all_transactions->latitude }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Longitude </h6>
											<p>{{ $all_transactions->longitude }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                       
										
									</ul>

                                    @if(isset($errorMessage))
                                        <div class="alert alert-danger">
                                            {{ $errorMessage }}
                                        </div>
                                    @endif

                                    @if(session()->has('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                @if (session()->has('success'))
                                    <div class="alert alert-danger">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                    @can('super_admin')
                                        @if (!$all_transactions->receiptno && $all_transactions->account_type == 'Prepaid')
                                         <button wire:click="" class="btn btn-xs btn-danger">&nbsp;</button> 
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
