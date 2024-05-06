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
                            <h4 class="card-title">Access Control List</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              
                            </div>
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Date</th>
                                  <th>Role</th>
                                  <th>User Count</th>
                                  <th>Guard Name </th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>

                             
                              @if(count($rolesWithUserCount) > 0)
                            

                                @foreach($rolesWithUserCount as $usr)
                                    <tr>
                                    <td>{{ \Carbon\Carbon::parse($usr->created_at)->format('Y-m-d H:i:s')}} </td>
                                    <td> {{ $usr->name }} </td>
                                    <td><div class="badge badge-success"> {{ $usr->users_count }} </div> </td>
                                    <td>{{ $usr->guard_name }} </td>
                                   
                                    <td>
                                        <!-- <a href="#" class="btn btn-primary btn-xs">View</a> -->
                                        <button href="/view_access_log/{{ $usr->id }}" wire:navigate class="btn btn-primary btn-xs">View</button>
                                    </td>
                                    </tr>

                                    @endforeach

                               
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Role Found </td>
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
