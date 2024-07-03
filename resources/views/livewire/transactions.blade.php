<div wire:poll>
    
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
                    <div class="col-12 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Latest Transactions</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown12" x-placement="left-start">
                                <a class="dropdown-item" href="#">Pending</a>
                                <a class="dropdown-item" href="#">Processing</a>
                                <a class="dropdown-item" href="#">Successful</a>
                              </div>
                            </div>
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Date</th>
                                  <th>Transaction ID</th>
                                  <th>Account No</th>
                                  <th>Meter No</th>
                                  <th>Customer Name</th>
                                  <th>Email</th>
                                  <th>Amount</th>
                                  <th>Acount Type</th>
                                  <th>Business Hub</th>
                                  <th>Status</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                              

                              @if(count($all_transactions['links']) > 0)

                              @foreach($all_transactions['data'] as $transaction)
                                <tr>
                                  <td> {{ \Carbon\Carbon::parse($transaction['created_at'])->format('Y-m-d H:i:s')}} </td>
                                  <td>{{ $transaction['transaction_id'] }} </td>
                                  <td>{{ $transaction['account_number'] }}</td>
                                  <td>{{ $transaction['meter_no'] }}</td>
                                  <td> <div class="text-dark font-weight-medium">{{ $transaction['customer_name'] }}</div> </td>
                                  <td>{{ $transaction['email'] }}</td>
                                  <td>₦{{ number_format($transaction['amount'], 2) }}</td>
                                  <td>{{ $transaction['account_type'] }}</td>
                                  <td>{{ $transaction['BUID'] }}</td>
                                  <td>
                                    @if($transaction['status'] == "started")
                                    <label class="badge badge-info">Started</label>
                                    @elseif($transaction['status'] == "processing")
                                    <label class="badge badge-warning">Processing</label>
                                    @elseif($transaction['status'] == "success")
                                    <label class="badge badge-success">Successful</label>
                                    @else
                                    <label class="badge badge-danger">Failed</label>
                                    @endif
                                  
                                  </td>
                                  <td>
                                    <a href="transaction_details/{{ $transaction['transaction_id'] }}" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                  </td>
                                </tr>

                                @endforeach

                                <nav>
                                    <ul class="pagination">
                                        @foreach($all_transactions['links'] as $link)
                                            <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                                                <!-- <a class="page-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a> -->
                                                <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </nav>

                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Transaction Found</td>
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
