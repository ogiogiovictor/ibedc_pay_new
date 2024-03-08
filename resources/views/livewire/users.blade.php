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
                            <h4 class="card-title">All Users</h4>
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
                                  <th>User ID</th>
                                  <th>User Name</th>
                                  <th>Email </th>
                                  <th>Phone</th>
                                  <th>Pin</th>
                                  <th>User Code</th>
                                  <th>Primary Meters</th>
                                  <th>Authority</th>
                                  <th>Status</th>
                                  <th>Actions</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if($users->count() > 0)

                              @foreach($users as $user)
                                <tr>
                                  <td>{{ $user->created_at }} </td>
                                  <td>{{ $user->id }} </td>
                                  <td><div class="text-dark font-weight-medium"> {{ $user->name }} </div></td>
                                  <td>{{ $user->email }}</td>
                                  <td> {{ $user->phone }} </td>
                                  <td>{{ $user->phon }}</td>
                                  <td>{{ $user->user_code }}</td>
                                  <td>{{ $user->meter_no_primary }}</td>
                                  <td>{{ $user->authority }}</td>
                                  <td>
                                    @if($user->status == 0)
                                    <label class="badge badge-warning">Inactive</label>
                                    @elseif($user->status == 1)
                                    <label class="badge badge-success">Active</label>
                                    @else
                                    <label class="badge badge-danger">Failed</label>
                                    @endif
                                  
                                  </td>
                                  <td>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-grease-pencil"></i></a>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-delete"></i></a>
                                  </td>
                                </tr>

                                @endforeach
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
