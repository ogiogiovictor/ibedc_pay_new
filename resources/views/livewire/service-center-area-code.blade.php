<div wire:poll>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


<!--                   
                    <x-topbar /> -->

                 
            <div class="tab-content tab-transparent-content pb-0">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">

                  <div class="row">
                
                 
                  
                  </div>


              

                  <div class="row">
                    <div class="col-12 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <p>
                            <h4 class="card-title">AREA CODE DASHBOARD </h4> <a href="area_code"> (Add Area Code) </a>  <hr/>
                            </p>
                          <div class="d-flex flex-wrap justify-content-between">
                            
                            <form class="form-inline justify-content-end" wire:submit.prevent="searchTransactions">
                             
                            @if (session()->has('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                           @endif

                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="AREA_CODE">Area Code</option>
                                  <option value="Service_Centre">Service Center Name</option>
                                  <option value="BHUB">Business Hub</option>
                                  <option value="State">State</option>
                                   <option value="BUID">BUID</option>
                                </select>
                              </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">Enter Value:</label>
                                <input type="text" class="form-control" wire:model="clearValue" id="inputField" placeholder="Enter value">
                              </div>
                              <button type="submit" class="btn btn-md btn-primary" wire:submit.prevent="searchTransactions">Search</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                           
                            </form>
                            
                          </div>
                          <hr/>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Date Created</th>
                                  <th>Business HUB</th>
                                  <th>BUID</th>
                                  <th>Area Code</th>
                                  <th>Service Center</th>
                                  <th>State</th>
                                  <th>Number of Accounts</th>
                                  <th>DTM</th>
                                </tr>
                              </thead>
                              <tbody>

                            
                             
                              @if(count($allcenters) > 0)

                              @foreach($allcenters as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at }}</td>
                                       <td><strong>{{ $transaction->BHUB }}</strong></td>
                                    <td>{{ $transaction->BUID }}</td>
                                    <td>{{ $transaction->AREA_CODE }}</td>
                                    <td>{{ $transaction->Service_Centre }}</td>
                                    <td>{{ $transaction->State }}</td>
                                    <td>{{ $transaction->number_of_customers }}</td>
                                    <td>{{ $transaction->dtm_emails }}</td>
                                  
                                </tr>

                                @endforeach
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Aread Code Found</td>
                                </tr>
                                @endif

                               

                              </tbody>
                            </table>

                            <!-- Add pagination links -->


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
