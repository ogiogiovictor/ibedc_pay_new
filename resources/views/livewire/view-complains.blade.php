<div wire:poll>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


                  
                    <!-- <x-topbar /> -->
                <h4>COMPLAIN DETAILS FOR ID {{ $contact->id }}</h4>
                 
            <div class="tab-content tab-transparent-content pb-0">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                 

                  <div class="row">
                    <div class="col-8 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h6 class="card-title">Conmplain Details</h6><hr/>
                          
                          </div>
                          <div class="table-responsive">
                            
                          <ul class="bullet-line-list">
										<li>
											<h6>Date Logged</h6>
											<p>{{ $contact->created_at }} </p>
											<p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>User Name</h6>
											<p>{{ $contact->name }} </p>
											<p class="text-muted mb-4">
												<i class="mdi mdi-clock-outline"></i>
											</p>
										</li>

                                        <li>
											<h6>User Email</h6>
											<p>{{ $contact->email }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Account Type </h6>
											<p>{{ $contact->account_type }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Account No </h6>
											<p>{{ $contact->unique_code }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Subject </h6>
											<p>{{ $contact->subject }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Message </h6>
											<p>{{ $contact->message }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Status Code </h6>
											<p class="btn btn-warning">{{ $contact->status }} </p>
                                            <p class="text-muted mb-4"></p>
										</li>

                                        <li>
											<h6>Phone </h6>
                                            <p>{{ $contact->phone }}</p>
                                            <p class="text-muted mb-4"></p>
										</li>

									</ul>

                                    <div>

                                    <form  wire:submit.prevent="resolveIssue">
                                    <div class="d-flex flex-wrap" id="contact_form">
                                    
                                        <h1>Ready to talk?</h1><hr/>
                                        @if (session()->has('error'))
                                            <div class="alert alert-danger">
                                                {{ session('error') }}
                                            </div>
                                        @endif

                                        @if (session()->has('success'))
                                            <div class="alert alert-danger">
                                                {{ session('success') }}
                                            </div>
                                        @endif
                                       
                                        <div class="col-sm-12 form-group"><br/><br/>
                                            <textarea class="form-controls" wire:model="mymsessage" placeholder="Message" rows="5"></textarea>
                                             <!-- <input type="text" wire:model="email" value="{{ $contact->email }}"/> -->
                                             <!-- <input type="text" wire:model="email" placeholder="Email"/> -->
                                        </div>
                                        <div class="col-sm-12 form-group"><button class="btn btn-primary pull-right" type="submit">Send</button></div>
                                        </div>
                                    </div>
</form>

                                 

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
