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
                            <h4 class="card-title">All Users <a href="add_users"><hr/> (Add Users) </a> </h4>
                             @if (session()->has('success'))
                            <div class="alert alert-danger">
                                {{ session('success') }}
                            </div>
                        @endif
                            

                            <form class="form-inline justify-content-end" wire:submit.prevent="searchUser">
                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="email">Email</option>
                                  <option value="name">Customer Name</option>
                                  <option value="phone">Customer Phone</option>
                                  <option value="meter_no_primary">Meter/Account No</option>
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
                                  <!-- <th>User ID</th> -->
                                  <th>User Name</th>
                                  <th>Email </th>
                                  <th>Phone</th>
                                  <!-- <th>Account Type</th> -->
                                  <!-- <th>User Code</th> -->
                                  <th>Primary Meters</th>
                                  <th>Authority</th>
                                  <th>Status</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(count($users['links']) > 0)

                              @foreach($users['data'] as $user)
                                <tr>
                                  <td>{{ \Carbon\Carbon::parse($user['created_at'])->format('Y-m-d H:i:s')}} </td>
                                  <!-- <td>{{ $user['id'] }} </td> -->
                                  <td><div class="text-dark font-weight-medium"> {{ $user['name'] }} </div></td>
                                  <td>{{ $user['email'] }}</td>
                                  <td> {{ $user['phone'] }} </td>
                                  <!-- <td>{{ $user['account_type'] }}</td> -->
                                  <!-- <td>{{ $user['user_code'] }}</td> -->
                                  <td>{{ $user['meter_no_primary'] }}</td>
                                  <td>{{ $user['authority'] }}</td>
                                  <td>
                                    @if($user['status'] == 0)
                                    <label class="badge badge-warning">Inactive</label>
                                    @elseif($user['status'] == 1)
                                    <label class="badge badge-success">Active</label>
                                    @else
                                    <label class="badge badge-danger">Failed</label>
                                    @endif
                                  
                                  </td>
                                  <td>
                                    <!-- <a href="/view_user/{{ $user['id'] }}" class="mr-1 p-2 btn btn-xs btn-danger" placeholder="Reset Password">Reset</a> -->
                                    <button class="btn btn-xs btn-danger" wire:click="resetUser( {{ $user['id'] }} )" class="btn btn-xs btn-danger">Reset</button>
                                  </td>
                                </tr>

                                @endforeach

                                <nav>
                                    <ul class="pagination">
                                        @foreach($users['links'] as $link)
                                            <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                                                <!-- <a class="page-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a> -->
                                                <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </nav>

                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No User Found</td>
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
