<div>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


                  
                    <!-- <x-topbar /> -->

                 
            <div class="tab-content tab-transparent-content pb-0">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                 

                  <div class="row">
                   
                    <div class="col-6 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Add User</h4>
                          </div>
                    
                    <div class="col-12 grid-margin">
                            
                <form class="form-sample" wire:submit.prevent="addUser">

                          @if(isset($errorMessage))
                            <div class="alert alert-danger">
                                {{ $errorMessage }}
                            </div>
                        @endif

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


                        

                    <p class="card-description">
                      <!-- Error Information is Displayed Here -->
                    </p>
                    <div class="row">


                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">User Name</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="user_name" />
                            @error('user_name') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">User Email</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="user_email" />
                            @error('user_email') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">User Phone</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="user_phone" />
                            @error('user_phone') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                    </div>

                
                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Region</label>
                          <div class="col-sm-9">
                            
                          <select name="bhub" class="form-control" wire:model="region">
                          <option value="">Select Region</option>
                          <option value="HQ">HQ</option>
                          <option value="OGUN">OGUN</option>
                          <option value="KWARA">KWARA</option>
                          <option value="OSUN">OSUN</option>
                          <option value="OYO">OYO</option>
                          <option value="IBADAN">IBADAN</option>
                           
                        </select>
                        @error('region') <small class="text-danger">{{ $message }}</small>@enderror

                          </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">User Account Type</label>
                          <div class="col-sm-9">
                            
                          <select name="account_type" class="form-control" wire:model="account_type">
                          <option value="">Select Type</option>
                          <option value="Prepaid">Prepaid</option>
                          <option value="Postpaid">Postpaid</option>
                        </select>
                        @error('region') <small class="text-danger">{{ $message }}</small>@enderror

                          </div>
                        </div>
                    </div>



                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Business Hub</label>
                          <div class="col-sm-9">
                            
                          <select name="bhub" class="form-control" wire:model="bhub">
                          <option value="">Select Business Hub</option>
                            @foreach($buid as $b)
                              
                                <option value="{{ $b->Name }}">{{ $b->Name }}</option>
                            @endforeach
                           
                        </select>
                        @error('bhub') <small class="text-danger">{{ $message }}</small>@enderror

                          </div>
                        </div>
                    </div>



                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Service Center</label>
                          <div class="col-sm-9">
                            
                          <select name="service_center" class="form-control" wire:model="service_center">
                          <option value="">Select Service Center</option>
                            @foreach($get_service as $b)
                              
                                <option value="{{ $b->DSS_11KV_415V_Owner }}">{{ $b->DSS_11KV_415V_Owner }}</option>
                            @endforeach
                           
                        </select>
                        @error('service_center') <small class="text-danger">{{ $message }}</small>@enderror

                          </div>
                        </div>
                    </div>



                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">User Role</label>
                          <div class="col-sm-9">
                            
                          <select name="authority" class="form-control" wire:model="authority">
                          <option value="">Select Role</option>
                          <option value="customer">Customer</option>
                          <option value="dtm">DTM</option>
                          <option value="rico">Customer Care</option>
                          <option value="billing">Billing</option>
                           <option value="rico">RICO</option>
                            <option value="agent">Agent</option>
                        </select>
                        @error('authority') <small class="text-danger">{{ $message }}</small>@enderror

                          </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Password</label>
                          <div class="col-sm-9">
                            <input type="password" class="form-control" wire:model="password" />
                            @error('password') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                    </div>



                    </div>


                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group row">
                          <!-- <button class="btn btn-block btn-primary" type="submit">Submit</button>  -->

                          <button type="submit"
                             class="btn btn-block btn-primary"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>Submit</span>
                            <span wire:loading>Processing…</span>
                        </button>
                         
                       

                        </div>
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

