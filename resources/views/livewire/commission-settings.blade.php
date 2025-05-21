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
                    <div class="col-6 grid-margin">
                      <div class="card">
                       
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Target Information</h4>
                            
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                 <tr>
                                  <th>ID</th>
                                  <th>Commission Name</th>
                                  <th>Percentage</th>
                                  <th>Appplied </th>
                                  <th>Action </th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(!empty($commission))

                              @foreach($commission as $tag)
                                <tr>
                                  <td>{{ $tag->id }} </td>
                                  <td>{{ $tag->name }} </td>
                                  <td><div class="text-dark font-weight-medium badge badge-warning"> {{ number_format($tag->percentage, 2) }} </div></td>
                                  <td><div class="text-dark font-weight-medium badge badge-warning"> {{ $tag->appied_to }} </div></td>
                                 
                                  <td><a href="#" wire:navigate class="mr-1 p-2 btn btn-xs btn-primary">Edit Commision</a> </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Commission Found </td>
                                </tr>
                                @endif

                              </tbody>
                            </table>
                          </div>
                        </div>

                      </div>
                    </div>



                    

                    <div class="col-6 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Add Targets</h4>
                          </div>
                    
                    <div class="col-12 grid-margin">
                            
                <form class="form-sample" wire:submit.prevent="addTarget">

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
                          <label class="col-sm-3 col-form-label">Applied To</label>
                          <div class="col-sm-9">
                           <input type="text"  class="form-control"/>

                          </div>
                        </div>
                      </div>


                      <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Percentage</label>
                          <div class="col-sm-9">
                          <input type="text" class="form-control"/>
                          </div>
                        </div>
                      </div>

                      


                    </div>


                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group row">
                          <button class="btn btn-xs btn-primary" type="submit">Update</button> 

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

