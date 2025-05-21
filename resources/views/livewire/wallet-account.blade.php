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
                            <h4 class="card-title">All Users Wallet</h4>

                            <form class="form-inline justify-content-end" wire:submit.prevent="searchUserWallet">
                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="user_id">Email</option>
                                </select>
                              </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">Enter Value:</label>
                                <input type="text" class="form-control" wire:model="clearValue" id="inputField" placeholder="Enter value">
                              </div>
                              <button type="submit" class="btn btn-md btn-primary">Search</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                              @if (session()->has('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                              @endif
                            </form>

                            
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Date</th>
                                  <th>User ID</th>
                                  <th>User Name</th>
                                  <th>Wallet Amount</th>
                                  <th>Commission </th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(!empty($users['data']) && count($users['data']) > 0)
                                @foreach($users['data'] as $user)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($user['created_at'])->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $user['user_id'] }}</td>
                                        <td>{{ \App\Models\User::where('id', $user['user_id'])->value('name') }}</td>
                                        <td>
                                            <div class="text-dark font-weight-medium btn btn-sm btn-success">
                                                <b>{{ number_format($user['wallet_amount'], 2) }}</b>
                                            </div>
                                        </td>
                                        <td>{{ $user['commission_amount'] }}</td>
                                        <td>
                                            <a href="/view_wallet/{{ $user['id'] }}/{{ $user['user_id'] }}" class="mr-1 p-2 btn btn-xs btn-primary">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">No User Wallet Found</td>
                                </tr>
                            @endif

                                <nav>
                                @if(!empty($users['links']))
        <ul class="pagination">
            @foreach($users['links'] as $link)
                <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                    <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                </li>
            @endforeach
        </ul>
    @endif
                                </nav>

                               

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
