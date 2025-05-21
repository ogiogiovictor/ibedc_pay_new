<div wire:poll>
    
<x-navbar />


<div class="container-fluid page-body-wrapper"> 

         <x-sidebar />

         <div class="main-panel">


         <div class="content-wrapper">
            <div class="row">

                <div class="col-md-12">

                <h4>Wallet & Virtual Account History FOR {{ $data['user']->name }}  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="/transactions" wire:navigation> << Back</a></h4>


                    <div class="tab-content tab-transparent-content pb-0">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">


                        <div class="row">
                            <div class="col-8 grid-margin">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap justify-content-between"><h6 class="card-title">Wallet Details</h6><hr/><br/></div>


                                        <div class="table-responsive">

                                            <div class="bullet-line-list">

                                                            
                                                <div>
                                                    <h1>Wallet Account</h1>
                                                    <p>User ID: {{ $data['user']->id ?? 'N/A' }}</p>
                                                    <p>User Name: {{ $data['user']->name ?? 'N/A' }}</p>
                                                    <p>Wallet Balance: ₦ {{ number_format($data['wallet']->wallet_amount, 2) ?? 'N/A' }}</p>
                                                    <p>Virtual Account Number: {{ $data['virtualAccount']->account_no ?? 'N/A' }}</p>
                                                    <p>Virtual Account Email: {{ $data['virtualAccount']->customer_email ?? 'N/A' }}</p>
                                                    <p>Virtual Account Name: {{ $data['virtualAccount']->account_name ?? 'N/A' }}</p>

                                                </div>



                                                <hr/>
                                                <div>
                                                
                                                    <h2>Wallet History</h2>
                                                    <table class="table table-bordered">

                                                    <tr>
                                                        <th>TransactionID</th>
                                                        <th>Created At</th>
                                                        <th>Price</th>
                                                        <th>Entry</th>
                                                        <th>Provider Reference</th>
                                                        <th>status</th>

                                                    </tr>

                                                    @foreach ($data['userwallethistory'] as $transaction)
                                                    <tr>
                                                        <td>{{ $transaction->transactionId }}</td>
                                                        <td>{{ $transaction->created_at }}</td>
                                                        <td> ₦{{ number_format($transaction->price, 2) }}</td>
                                                        <td>{{ $transaction->entry }}</td>
                                                        <td>{{ $transaction->provider_reference }}</td>
                                                        <td>{{ $transaction->status }}</td>
                                                    <tr>
                                                
                                                    @endforeach

                                                    </table>
                                                
                                                </div>



                                                 <hr/>



                                            <div>
                                               
                                               <h2>Virtual Transactions</h2>
                                               <table class="table table-bordered">

                                               <tr>
                                                   <!-- <th>ID</th> -->

                                                   <th>Created At</th>
                                                   <th>Transaction Ref</th>
                                                   <th>Reference</th>
                                                   <th>Amount</th>
                                                   <!-- <th>Customer Name</th> -->
                                                   <th>status</th>

                                               </tr>

                                               @foreach ($data['virtualAccountTransactions'] as $transaction)
                                               <tr>
                                                   <!-- <td>{{ $transaction->fid }}</td> -->

                                                   <td>{{ $transaction->created_at }}</td>
                                                   <td>{{ $transaction->tx_ref }}</td>
                                                   <td>{{ $transaction->flw_ref }}</td>
                                                   <td> ₦{{ number_format($transaction->amount, 2) }}</td>
                                                   <!-- <td>{{ $transaction->customer_name }}</td> -->
                                                   <td>{{ $transaction->status }}</td>
                                               <tr>
                                              
                                                @endforeach

                                               </table>
                                             
                                           </div>


                                           <hr/>


                                           <div>
                                               
                                               <h2>Payment History</h2>
                                               <table class="table table-bordered table-responsive">

                                               <tr>
                                                
                                                   <th>Created At</th>
                                                   <th>Transaction ID</th>
                                                   <th>Account Type</th>
                                                   <th>Account No</th>
                                                   <th>Meter No</th>
                                                   <th>Amount</th>
                                                   <th>Customer Name</th>
                                                   <th>Provider</th>
                                                   <th>Provider Reference</th>
                                                 
                                                   <th>Status</th>

                                               </tr>

                                               @foreach ($data['paymenthistory'] as $transaction)
                                               <tr>
                                                  
                                                   
                                                   <td>{{ $transaction->created_at }}</td>
                                                   <td>{{ $transaction->transaction_id }}</td>
                                                   <td>{{ $transaction->account_type }}</td>
                                                   <td>{{ $transaction->account_number }}</td>
                                                   <td>{{ $transaction->meter_no }}</td>
                                                   <td> ₦{{ number_format($transaction->amount, 2) }}</td>
                                                   <td>{{ $transaction->customer_name }}</td>
                                                   <td>{{ $transaction->provider }}</td>
                                                   <td>{{ $transaction->providerRef }}</td>
                                                   <td>{{ $transaction->status }}</td>
                                               <tr>
                                              
                                                @endforeach

                                               </table>
                                             
                                           </div>

                                           
                                     

                                            </div>


                                            @if(isset($errorMessage))
                                                                            <div class="alert alert-danger">
                                                                                {{ $errorMessage }}
                                                                            </div>
                                                                        @endif

                                                                        @if(session()->has('error'))
                                                                        <div class="alert alert-danger">
                                                                            {{ session('error') }}
                                                                        </div>
                                                                    @endif

                                                                    @if (session()->has('success'))
                                                                        <div class="alert alert-danger">
                                                                            {{ session('success') }}
                                                                        </div>
                                                                    @endif





                                                                  





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



</h2>