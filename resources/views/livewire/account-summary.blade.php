<div>
    <x-navbar />
    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">
            <div class="content-wrapper">

                <!-- 🟢 Title Section -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h3 class="font-weight-bold text-primary">
                            {{ auth()->user()->region === 'HQ' ? 'All DTM Regions Summary' : auth()->user()->region . ' DTM Regional Summary' }}
                        </h3>
                        <p class="text-muted mb-0">Overview of total records grouped by region</p>
                    </div>
                </div>

                <!-- 🟩 Region Summary Cards -->
                <div class="row">
                    @foreach($regionCounts as $item)
                        <div class="col-12 col-sm-6 col-md-6 col-xl-3 grid-margin stretch-card">
                            <div class="card"  style="cursor:pointer;">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <h4 class="card-title">{{ ucwords($item->region ?? 'Pending Alignment') }}</h4>
                                        <div class="dropdown dropleft card-menu-dropdown">
                                            <i class="mdi mdi-dots-vertical card-menu-btn"></i>
                                        </div>
                                    </div>

                                    <div class="pt-2">
                                        <h2 class="mr-3 text-info">{{ $item->total }}</h2>
                                        <p class="text-muted font-weight-bold text-small mb-0">
                                            <span class="font-weight-normal">{{ now()->format('Y-m-d') }}</span>
                                        </p>
                                    </div>

                                    <button class="btn btn-outline-secondary btn-sm mt-2">
                                        <i class="mdi mdi-eye mr-1"></i> View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- 🟦 Business Hub Table -->
                @if(!empty($hubDetails) || $isSearching)
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title text-primary mb-3">
                                        {{ ucwords($selectedRegion) }} — Business Hub Summary
                                    </h4>

                                  <form class="form-inline justify-content-end" wire:submit.prevent="searchTransactions">
                             
                            @if (session()->has('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                             
                              <div class="form-group mr-2">
                                <label for="selectOption" class="mr-2">Select:</label>
                                <select class="form-control" id="selectOption" wire:model="clearOption">
                                  <option value="">Select</option>
                                  <option value="business_hub">Business Hub</option>
                                  <option value="service_center">Service Center</option>
                                </select>
                              </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                              <div class="form-group mr-2">
                                <label for="inputField" class="mr-2">Enter Value:</label>
                                <input type="text" class="form-control" wire:model="clearValue" id="inputField" placeholder="Enter value">
                              </div>
                              <button type="submit" class="btn btn-md btn-primary" wire:submit.prevent="searchTransactions">Search</button>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
                             
                            </form>


                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    
                                                    <th>Business Hub</th>
                                                    <th>Service Center</th>
                                                    <th>DTM Officer</th>
                                                    <th>Email</th>
                                                    <th>Pending</th>
                                                    <th>Rejected</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($hubDetails as $hub)
                                                    <tr>
                                                        
                                                        <td>{{ $hub->business_hub }}</td>
                                                        <td>{{ $hub->service_center }}</td>
                                                        <td>{{ $hub->user_name ?? '-' }}</td>
                                                        <td>{{ $hub->user_email ?? '-' }}</td>
                                                        <td>{{ $hub->pending }}</td>
                                                        <td>{{ $hub->rejected }}</td>
                                                        <td>{{ $hub->total_count }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted">No records found for this region</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-right mt-3">
                                        <button wire:click="refreshCounts" class="btn btn-secondary btn-sm">
                                            <i class="mdi mdi-refresh mr-1"></i> Refresh Summary
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <x-footer />
        </div>
    </div>
</div>
