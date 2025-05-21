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
                            <h4 class="card-title">Audit Logs</h4>

                            <form class="form-inline justify-content-end" wire:submit.prevent="searchUserAccount">
                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="customer_email">Email</option>
                                  <option value="account_no">Account No</option>
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
                                  <th>User Name</th>
                                  <th>IP Address</th>
                                  <th>Method</th>
                                  <th>Message </th>
                                  <!-- <th>User Agent </th> -->
                                  <th>Status </th>
                                 
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(count($all_logs['links']) > 0)

                              @foreach($all_logs['data'] as $vaccount)

                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($vaccount['created_at'])->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ \App\Models\User::where('id', $vaccount['user_id'])->value('name') }}</td>
                                        <td>{{ $vaccount['ip_address'] }}</td>
                                        <td>{{ $vaccount['method'] }}</td>
                                        <td>{{ $vaccount['response'] }}</td>
                                        <!-- <td>{{ $vaccount['user_agent'] }}</td> -->
                                        <td>{{ $vaccount['status_code'] }}</td>
                                      
                                        <td>
                                            <a href="/view_user_virtual_account/{{ $vaccount['id'] }}" class="mr-1 p-2 btn btn-xs btn-primary">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">No Log Found</td>
                                </tr>
                            @endif

                                
                            <nav>
                                    <ul class="pagination">
                                        @foreach($all_logs['links'] as $link)
                                            <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                                                <!-- <a class="page-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a> -->
                                                <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                                            </li>
                                        @endforeach
                                    </ul>
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
