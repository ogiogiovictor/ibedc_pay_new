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
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Pending Account Request <strong><span style="color:red"> ( URGENT!!! )</span></strong></h4>
                            <form class="form-inline justify-content-end" wire:submit.prevent="searchTransactions">
                             
                            @if (session()->has('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">From:</label>
                                <input type="date" class="form-control" wire:model="fromdate" id="fromdate">
                              </div>
                                <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">To:</label>
                                <input type="date" class="form-control" wire:model="todate" id="todate">
                              </div>
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="tracking_id">Tracking ID</option>
                                  <option value="surname">Surname</option>
                                </select>
                              </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">Enter Value:</label>
                                <input type="text" class="form-control" wire:model="clearValue" id="inputField" placeholder="Enter value">
                              </div>
                              <button type="submit" class="btn btn-md btn-primary" wire:submit.prevent="searchTransactions">Search</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                              <button type="button" class="btn btn-md btn-secondary" wire:click="exportTransactions">Export </button>
                              <!-- <button type="submit" class="btn btn-md btn-secondary" wire:click="exportTransactions">Export</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; -->
                            
                            </form>
                            
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Applied Date</th>
                                  <th>Tracking Number</th>
                                  <th>Customer Name</th>
                                  <!-- <th>Latitude</th>
                                  <th>Longitude</th> -->
                                  <th>Region</th>
                                  <th>Business Hub</th>
                                  <th>Service Center</th>
                                  <th>Status</th>
                                  <th>Account No</th>
                                  <th>Date Past</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                            
                              @if($accounts->count() > 0)

                              @foreach($accounts as $transaction)
                                <tr>
                                  <td> {{ $transaction->created_at }} </td>
                                  <td><strong>{{ $transaction->tracking_id }}</strong></td>
                                  <td> {{ $transaction->customer?->surname }}  {{ $transaction->customer?->firstname }}  {{ $transaction->customer?->other_name }} </td>
                                  <!-- <td>{{ $transaction->latitude }} </td>
                                  <td>{{ $transaction->longitude }} </td> -->
                                  <td>{{ $transaction->region }}</td>
                                  <td>{{ $transaction->business_hub }}</td>
                                  <td>{{ $transaction->service_center }}</td>
                                  <td>
                                      @if($transaction->status == "0")
                                      <label class="badge badge-info">Started</label>
                                      @elseif($transaction->status == "1")
                                      <label class="badge badge-warning">Processing</label>
                                      @elseif($transaction->status == "2")
                                      <label class="badge badge-warning">with Billing</label>
                                      @elseif($transaction->status == "4")
                                      <label class="badge badge-success">Completed</label>
                                       @elseif($transaction->status == "3")
                                      <label class="badge badge-warning">Rejected</label>
                                      @else
                                      <label class="badge badge-danger">N/A</label>
                                      @endif
                                  
                                  </td>
                                   <td>{{ $transaction->account_no }}</td>
                                  <td>
                                      {{ $transaction->status == '0'
                                          ? \Carbon\Carbon::parse($transaction->created_at)->diffForHumans()
                                          : \Carbon\Carbon::parse($transaction->updated_at)->diffForHumans() }}
                                  </td>
                                 
                                
                                  
                                  <td>
                                    <!-- <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a> -->
                                     @canany(['super_admin', 'dtm', 'billing', 'bhm', 'rico', 'audit'])
                                    <a href="account_details/{{ $transaction->tracking_id }}" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                     @endcanany

                                    @canany(['super_admin', 'mso'])
                                    <a href="{{ url('evaluation/' . $transaction->tracking_id) }}" class="btn btn-primary btn-sm mr-1">
                                        Technical Evaluate
                                    </a>
                                @endcanany

                                  </td>
                                </tr>

                                @endforeach
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Customer Found</td>
                                </tr>
                                @endif

                               

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
