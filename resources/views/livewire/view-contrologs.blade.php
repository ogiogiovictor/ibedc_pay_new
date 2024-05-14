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
                <div class="tab-pane fade show active" id="overview" role="" aria-labelledby="overview-tab">
                 

                <form class="form-sample" wire:submit.prevent="assignMenu">
                @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                  <div class="row">
                

                    <div class="col-6 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">ACCESS CONTROL CONFIGURATION FOR {{ str_replace("_", " ", strtoupper($role->name)) }}</h4>
                           
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Menu Name</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(count($menus) > 0)

                              @foreach($menus as $menu)
                                <tr>
                                  <td>
                                    <h3> 
                                        <!-- {{ $user_menu_access }} -->
                                        <input type="checkbox"  value="{{$menu['id'] }}" @if(in_array($menu['id'], explode("," , $user_menu_access))) checked @endif  wire:model="menu_name"/> &nbsp; {{ $menu['name'] }}  {{ $menu['id'] }} 
                                        @if(in_array($menu['id'], explode("," , $user_menu_access))) 
                                        <input type="text" value="Available" /> 
                                        
                                        @endif
                                    </h3>
                                    <table>
                                        <tr>
                                        <td>
                                        @if(!empty($menu['submenus']))
                                            @foreach($menu['submenus'] as $sub)
                                             {{ $sub['name'] }} &nbsp;&nbsp;
                                                <!-- <input type="checkbox"  /> {{ $sub['name'] }} &nbsp;&nbsp; -->
                                            @endforeach
                                        @endif
                                        </td>
                                        </tr>
                                    </table>
                                </td>
                                </tr>

                                @endforeach
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Menu Found </td>
                                </tr>
                                @endif

                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>




                    <div class="col-6 grid-margin">
                        <div class="card">
                            <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between">
                                <h4 class="card-title">Add Menu To Role</h4>
                            
                            </div>
                            <div class="table">
                            
                        
                        <p class="card-description">
                        <!-- Error Information is Displayed Here -->
                        </p>
                        <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Role Name</label>
                            <div class="col-sm-9">
                                <select class="form-control" wire:model="role_name">
                                <option value="">Select</option>
                                <option value="{{ $role->id }}">{{ str_replace("_", " ", $role->name) }}<option>
                        
                                </select>
                            </div>
                            </div>
                        </div>
                        
                        </div>
                        <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                            <button class="btn btn-block btn-primary" type="submit">Submit</button>
                            
                            </div>
                        </div>
                        
                        </div>
                    


                    
                            </div>
                            </div>
                        </div>
                        </div>

                    
                    </div>
                    
                    </div>
                </form>
              
              </div>
                    
                                    

                    </div>
                </div>
             </div>

        <x-footer />

        </div>

    </div>

</div>
