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
                            <h4 class="card-title">Change Password</h4>
                          </div>
                    
                    <div class="col-12 grid-margin">
                            
                <form class="form-sample" wire:submit.prevent="changePassword">

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
                          <label class="col-sm-3 col-form-label">User Email</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="user_email" disabled />
                            @error('user_email') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Password</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="password" />
                            @error('password') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Confirm Password</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="password1" />
                            @error('password1') <small class="text-danger">{{ $message }} </small>@enderror
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

