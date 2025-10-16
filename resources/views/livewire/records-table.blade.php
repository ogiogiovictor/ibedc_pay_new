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
                                  

                  <div class="row">
                    <div class="col-12 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <p>
                            <h4 class="card-title">ACCOUNT STATUS : {{ ucfirst($status) }} Records  </h4>
                            </p>
                          <!-- <div class="d-flex flex-wrap justify-content-between">
                            
                          
                            
                          </div> -->
                          <hr/>
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

                            
                             
                              @if(count($accounts['links']) > 0)

                              @foreach($accounts['data'] as $transaction)
                                <tr>
                                  <!-- <td> {{ $transaction['created_at'] }} </td> -->
                                  <td>{{ \Carbon\Carbon::parse($transaction['created_at'])->timezone('Africa/Lagos')->format('Y-m-d H:i:s') }}</td>
                                  <td><strong>{{ $transaction['tracking_id'] }}</strong></td>
                                  <td> {{ $transaction['customer']['surname'] }}  {{ $transaction['customer']['firstname'] }}  {{ $transaction['customer']['other_name'] }} </td>
                                
                                  <td>{{ $transaction['region'] }}</td>
                                  <td>{{ $transaction['business_hub'] }}</td>
                                  <td>{{ $transaction['service_center'] }}</td>
                                  <td>
                                      @if($transaction['status'] == "0")
                                      <label class="badge badge-info">Started</label>
                                      @elseif($transaction['status'] == "1")
                                      <label class="badge badge-warning">with DTM</label>
                                      @elseif($transaction['status'] == "3")
                                      <label class="badge badge-warning">with Compliance</label>
                                      @elseif($transaction['status'] == "2")
                                      <label class="badge badge-warning">with Billing</label>
                                      @elseif($transaction['status'] == "4")
                                      <label class="badge badge-success">Completed</label>
                                       @elseif($transaction['status'] == "5")
                                      <label class="badge badge-danger">Rejected</label>
                                      @else
                                      <label class="badge badge-danger">N/A</label>
                                      @endif
                                  
                                  </td>
                                   <td>{{ $transaction['account_no'] }}</td>
                                  <td>
                                      {{ $transaction['status'] == '0'
                                          ? \Carbon\Carbon::parse($transaction['created_at'])->diffForHumans()
                                          : \Carbon\Carbon::parse($transaction['updated_at'])->diffForHumans() }}
                                  </td>
                                 
                                
                                  
                                  <td>
                                    <!-- <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a> -->
                                     @canany(['super_admin', 'dtm', 'billing', 'bhm', 'rico', 'audit'])
                                    <a href="/account_details/{{ $transaction['tracking_id'] }}" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                     @endcanany

                                    @canany(['super_admin', 'mso'])
                                    <!-- <a href="{{ url('evaluation/' . $transaction['tracking_id']) }}" class="btn btn-primary btn-sm mr-1">
                                        TE
                                    </a> -->
                                @endcanany

                                  </td>
                                </tr>

                                @endforeach

                              
                                  <nav>
                                    <ul class="pagination">
                                        @foreach($accounts['links'] as $link)
                                            <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                                                <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </nav>


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
