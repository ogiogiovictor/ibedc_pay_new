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
                            <h4 class="card-title">Customer Complaints</h4>
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
                                  <th>Customer Name</th>
                                  <th>Account</th>
                                  <th>Account Type </th>
                                  <th>Email </th>
                                  <th>Subject </th>
                                  <th>Status</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(count($complains['links']) > 0)
                            

                                @foreach($complains['data'] as $logs)
                                    <tr>
                                    <td>{{ \Carbon\Carbon::parse($logs['created_at'])->format('Y-m-d H:i:s')}} </td>
                                    <td>{{ $logs['name'] }} </td>
                                    <td> {{ $logs['unique_code'] }} </td>
                                    <td>{{ $logs['account_type'] }} </td>
                                    <td>{{ $logs['email'] }} </td>
                                    <td><div class="text-dark font-weight-medium badge badge-warning"> {{ $logs['subject'] }} </div></td>
                                    <td><div class="text-dark font-weight-medium badge badge-success"> {{ $logs['status'] }} </div> </td>
                                    
                                    <td>
                                        <!-- <a href="#" class="btn btn-primary btn-xs">View</a> -->
                                        <a href="/view_complaints/{{ $logs['id'] }}" wire:navigate class="mr-1 p-2 btn btn-xs btn-primary">View</a>
                                    </td>
                                    </tr>

                                    @endforeach

                               
                                  <!-- Output pagination links -->
                                <nav>
                                    <ul class="pagination">
                                        @foreach($complains['links'] as $link)
                                            <li class="page-item {{ $link['active'] ? 'active' : '' }}">
                                                <!-- <a class="page-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a> -->
                                                <a href="{{ $link['url'] }}" class="page-link">{!! $link['label'] !!}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </nav>
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Complain Found </td>
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
