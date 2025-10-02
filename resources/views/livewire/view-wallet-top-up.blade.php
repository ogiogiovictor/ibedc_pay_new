<div wire:poll>
    
    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">


                  
                    <!-- <x-topbar /> -->
                <h4>Wallet Top Up</h4>
                 
            <div class="tab-content tab-transparent-content pb-0">
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                 

                  <div class="row">
                    <div class="col-12 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h6 class="card-title">Conmplain Details</h6><hr/>
                          
                          </div>
                          <div class="table-responsive">
                            <div class="mb-3">
    <input type="text" wire:model.debounce.500ms="search"
           placeholder="Search by Name, Email, TX Ref or FLW Ref"
           class="form-control w-1/3">
</div>
                        <div>


                          <table class="table-auto w-full border border-gray-300">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="px-4 py-2">FID</th>
                <th class="px-4 py-2">TX Ref</th>
                <th class="px-4 py-2">FLW Ref</th>
                <th class="px-4 py-2">Amount</th>
                <th class="px-4 py-2">Customer Name</th>
                <th class="px-4 py-2">Customer Email</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Payload</th>
                <th class="px-4 py-2">Credit Status</th>
                <th class="px-4 py-2">Resync</th>
            </tr>
        </thead>
        <tbody>
           @forelse ($filteredPayments as $payment)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $payment['fid'] }}</td>
                    <td class="px-4 py-2">{{ $payment['tx_ref'] }}</td>
                    <td class="px-4 py-2">{{ $payment['flw_ref'] }}</td>
                    <td class="px-4 py-2">{{ $payment['amount'] }}</td>
                    <td class="px-4 py-2">{{ $payment['customer_name'] }}</td>
                    <td class="px-4 py-2">{{ $payment['customer_email'] }}</td>
                    <td class="px-4 py-2">{{ $payment['status'] }}</td>
                    <td class="px-4 py-2 truncate">{{ Str::limit($payment['payload'], 50) }}</td>
                    <td class="px-4 py-2">{{ $payment['credit_status'] }}</td>
                     <td class="px-4 py-2">  <button wire:click="reSync({{ $payment['fid'] }})" class="btn btn-md btn-danger">Resync</button></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center px-4 py-2">No payments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="flex justify-between items-center mt-4">
        <button wire:click="prevPage" @disabled($currentPage === 1) class="px-4 py-2 bg-gray-200 rounded">
            Previous
        </button>

        <span>Page {{ $currentPage }} of {{ $lastPage }}</span>

        <button wire:click="nextPage" @disabled($currentPage === $lastPage) class="px-4 py-2 bg-gray-200 rounded">
            Next
        </button>
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
             </div>

        <x-footer />

        </div>

    </div>


   

</div>





