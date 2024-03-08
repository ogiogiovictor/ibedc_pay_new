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
                            <h4 class="card-title">All System Logs</h4>
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
                                  <th>ID</th>
                                  <th>User ID</th>
                                  <th>Ajax </th>
                                  <th>URL </th>
                                  <th>Method </th>
                                  <th>UserAgent</th>
                                  <th>Status</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(count($all_logs['data']) > 0)

                              @foreach($all_logs['data'] as $logs)
                                <tr>
                                  <td>{{ \Carbon\Carbon::parse($logs['created_at'])->format('Y-m-d H:i:s')}} </td>
                                  <td>{{ $logs['id'] }} </td>
                                  <td> {{ $logs['user_id'] }} </td>
                                  <td>{{ $logs['ajax'] }} </td>
                                  <td>{{ $logs['url'] }} </td>
                                  <td><div class="text-dark font-weight-medium badge badge-warning"> {{ $logs['method'] }} </div></td>
                                  <td>{{ $logs['user_agent'] }} </td>
                                  <td><div class="text-dark font-weight-medium badge badge-primary"> {{ $logs['status_code'] }} </div> </td>
                                
                                  <td>
                                    <a href="#" class="mr-1 text-muted p-2"><i class="mdi mdi-dots-horizontal"></i></a>
                                  </td>
                                </tr>

                                @endforeach
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Log Found </td>
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
