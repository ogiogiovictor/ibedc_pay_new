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
                            <h4 class="card-title">Add Area Code</h4>
                          </div>
                    
                    <div class="col-12 grid-margin">
                            
                <form class="form-sample" wire:submit.prevent="addAreaCode">

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
                          <label class="col-sm-3 col-form-label">Region</label>
                          <div class="col-sm-9">
                            
                          <select name="bhub" class="form-control" wire:model="region">
                          <option value="">Select Region</option>
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
                          <label class="col-sm-3 col-form-label">Add Service Center</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="service_center" />
                            @error('service_center') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                      </div>


                      

                      <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Add Area Code</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="area_code" />
                            @error('area_code') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">DTM Emails</label>
                          <div class="col-sm-9">
                            <input type="text" class="form-control" wire:model="dtm_email" />
                            @error('dtm_email') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                      </div>


                    </div>


                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group row">
                          <button class="btn btn-block btn-primary" type="submit">Submit</button> 

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

