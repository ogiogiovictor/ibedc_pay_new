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
                    <div class="col-12 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Pending Transactions - IBEDCPay v1</h4>
                              <form class="form-inline justify-content-end" wire:submit.prevent="searchTransactions">
                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="meter_no">Meter No</option>
                                  <option value="account_number">Account Number</option>
                                  <option value="customer_name">Customer Name</option>
                                  <option value="transaction_id">Transaction ID</option>
                                  <option value="email">Email</option>
                                  <option value="amount">Amount</option>
                                  <option value="BUID">Business Hub</option>
                                  <option value="providerRef">Provider Reference</option>
                                </select>
                              </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">Enter Value:</label>
                                <input type="text" class="form-control" wire:model="clearValue" id="inputField" placeholder="Enter value">
                              </div>
                              <button type="submit" class="btn btn-md btn-primary">Search</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                              <button type="submit" class="btn btn-md btn-secondary" wire:click="exportTransactions">Export</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                              @if (session()->has('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                              @endif
                            </form>
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
                          <div class="table-responsive"  wire:poll>
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

                              
                              @if(collect($transactions)->isNotEmpty())

                              @foreach($transactions as $transaction)
                                <tr>
                                  <td>{{ $transaction->created_at }} </td>
                                  <td>{{ $transaction->transaction_id }} </td>
                                  <td>{{ $transaction->account_number }}</td>
                                  <td>{{ $transaction->meter_no }}</td>
                                  <td> <div class="text-dark font-weight-medium">{{ $transaction->customer_name }}</div> </td>
                                  <td>{{ $transaction->email }}</td>
                                  <td>{{ number_format($transaction->amount, 2) }}</td>
                                  <td>{{ $transaction->account_type }}</td>
                                  <td>{{ $transaction->BUID }}</td>
                                  <td>
                                    @if($transaction->status == "started")
                                    <label class="badge badge-default">Started</label>
                                    @elseif($transaction->status == "processing")
                                    <label class="badge badge-warning">Processing</label>
                                    @elseif($transaction->status == "pending")
                                    <label class="badge badge-warning">Pending</label>
                                    @elseif($transaction->status == "success")
                                    <label class="badge badge-success">Successful</label>
                                    @else
                                    <label class="badge badge-danger">Failed</label>
                                    @endif
                                  
                                  </td>
                                  <td>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                  </td>
                                </tr>

                                @endforeach
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
