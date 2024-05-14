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
                    <div class="col-6 grid-margin">
                      <div class="card">
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Agency Information</h4>
                            <div class="dropdown dropleft card-menu-dropdown">
                              <button class="btn p-0" type="button" id="dropdown12" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdown12" x-placement="left-start">
                                <a class="dropdown-item" href="#">Add Role</a>
                              </div>
                            </div>
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>ID</th>
                                  <th>Agency Code</th>
                                  <th>Agency Name</th>
                                  <th>Date Created </th>
                                  <th>No Of Agents</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(!empty($agencies))
                                <tr>
                                  <td>{{ $agencies->id }} </td>
                                  <td>{{ $agencies->agent_code }} </td>
                                  <td><div class="text-dark font-weight-medium badge badge-warning"> {{ $agencies->agent_name }} </div></td>
                                  <td>{{ $agencies->created_at }} </td>
                                  <td>{{ $agencies->no_of_agents }} </td>
                                </tr>
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Information Found </td>
                                </tr>
                                @endif

                              </tbody>
                            </table>
                          </div>
                        </div>


                        <hr/>
                        <div class="card-body">
                          <div class="d-flex flex-wrap justify-content-between">
                            <h4 class="card-title">Target Information</h4>
                            
                          </div>
                          <div class="table-responsive">
                            <table class="table center-aligned-table">
                              <thead>
                                <tr>
                                  <th>Agency </th>
                                  <th>Year</th>
                                  <th>Month </th>
                                  <th>Monthly Target</th>
                                  <th>Monthly Collection</th>
                                  <!-- <th>Date</th> -->
                                  <td>Action</th>
                                </tr>
                              </thead>
                              <tbody>

                              @if(!empty($target))

                              @foreach($target as $tag)
                                <tr>
                                  <td>{{ \App\Models\Agency\Agents::where("id", $tag->agency_id)->value("agent_name") }} </td>
                                  <td>{{ $tag->year }} </td>
                                  <td>{{ $tag->month }} </td>
                                  <td><div class="text-dark font-weight-medium badge badge-warning"> {{ $tag->target_amount }} </div></td>
                                  <td><div class="text-dark font-weight-medium badge badge-success"> 
                                  {{
                                        number_format(\App\Models\Transactions\PaymentTransactions::where("agency", $tag->agency_id)
                                         ->whereIn("status", ['processing', 'success'])
                                        ->whereRaw('YEAR(created_at) = ?', [$tag->year])
                                        ->whereRaw('MONTH(created_at) = ?', [$tag->month])
                                        ->sum(\DB::raw('CONVERT(decimal(18,2), amount)')), 2)
                                    }} 
                                     <!-- {{ \App\Models\Transactions\PaymentTransactions::where(["agency" => $tag->agency_id, "created_at" => 
                                      $tag->created_at->format('Y')])->sum(\DB::raw('CONVERT(decimal(18,2), amount)')) }}  -->
                                  </div></td>
                                  <!-- <td>{{ $tag->created_at->format('Y-m-d') }} </td> -->
                                  <td><a href="#" wire:navigate class="mr-1 p-2 btn btn-xs btn-primary">Edit Target</a> </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                  <td colspan="10" class="text-center">No Target Found </td>
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
                            <h4 class="card-title">Add Targets</h4>
                          </div>
                    
                    <div class="col-12 grid-margin">
                            
                <form class="form-sample" wire:submit.prevent="addTarget">

                          @if(isset($errorMessage))
                            <div class="alert alert-danger">
                                {{ $errorMessage }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if (session()->has('success'))
                            <div class="alert alert-danger">
                                {{ session('success') }}
                            </div>
                        @endif


                        

                    <p class="card-description">
                      <!-- Error Information is Displayed Here -->
                    </p>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Year</label>
                          <div class="col-sm-9">
                            
                          <select name="year" class="form-control" wire:model="tyear">
                          <option value="">Select Year</option>
                            @foreach($years as $year)
                              
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        @error('year') <small class="text-danger">{{ $message }}</small>@enderror

                          </div>
                        </div>
                      </div>


                      <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Month</label>
                          <div class="col-sm-9">
                            <select name="month" class="form-control" wire:model.lazy="tmonth">
                            <option value="">Select Month</option>
                                @foreach($months as $key => $month)
                                    
                                    <option value="{{ $key }}">{{ $month }}</option>
                                @endforeach
                            </select>
                            @error('month') <small class="text-danger">{{ $message }}</small>@enderror
                          </div>
                        </div>
                      </div>

                      <div class="col-md-12">
                        <div class="form-group row">
                          <label class="col-sm-3 col-form-label">Month Target</label>
                          <div class="col-sm-9">
                            <input type="number" class="form-control" wire:model="mtarget" />
                            <input type="hidden" name="agency_id" value="{{ $agencies->id }}" />
                            @error('mtarget') <small class="text-danger">{{ $message }} </small>@enderror
                          </div>
                        </div>
                      </div>


                    </div>


                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group row">
                          <button class="btn btn-block btn-primary" type="submit">Create</button> 

                        </div>
                      </div>
                     
                    </div>




                  </form>


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

