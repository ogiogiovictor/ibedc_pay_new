<div>

    <x-navbar />

    <div class="container-fluid page-body-wrapper">
        <x-sidebar />

        <div class="main-panel">

            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">

                        <div class="tab-content tab-transparent-content pb-0">
                            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">

                                <div class="row">

                                    <!-- ================= LEFT FORM - CREDIT WALLET ================= -->
                                    <div class="col-6 grid-margin">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex flex-wrap justify-content-between">
                                                    <h4 class="card-title">Credit Wallet</h4>
                                                </div>

                                                <div class="col-12 grid-margin">
                                                    <form class="form-sample" wire:submit.prevent="creditWallet">

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
                                                            <div class="alert alert-success">
                                                                {{ session('success') }}
                                                            </div>
                                                        @endif

                                                        <div class="row">

                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label">User Email</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control" wire:model="email" />
                                                                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label">Amount</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control" wire:model="amount" />
                                                                        @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label">Transaction Reference</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control" wire:model="transref" />
                                                                        @error('transref') <small class="text-danger">{{ $message }}</small> @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label">Provider Reference</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control" wire:model="providerRef" />
                                                                        @error('providerRef') <small class="text-danger">{{ $message }}</small> @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <button class="btn btn-block btn-primary" type="submit">Submit</button>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- ================= RIGHT FORM - DEFAULT CREDIT WALLET ================= -->
                                    <div class="col-6 grid-margin">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex flex-wrap justify-content-between">
                                                    <h4 class="card-title">Default Credit Wallet</h4>
                                                </div>

                                                <div class="col-12 grid-margin">
                                                    <form class="form-sample" wire:submit.prevent="defaultCreditWallet">

                                                        <div class="row">

                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label">User Email</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control" wire:model="defaultEmail" />
                                                                        @error('defaultEmail') <small class="text-danger">{{ $message }}</small> @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label">Default Amount</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control" wire:model="defaultAmount" />
                                                                        @error('defaultAmount') <small class="text-danger">{{ $message }}</small> @enderror
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label">Transaction Reference</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control" wire:model="defaultTransref" />
                                                                        @error('defaultTransref') <small class="text-danger">{{ $message }}</small> @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="form-group row">
                                                                    <label class="col-sm-3 col-form-label">Provider Reference</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control" wire:model="defaultProviderRef" />
                                                                        @error('defaultProviderRef') <small class="text-danger">{{ $message }}</small> @enderror
                                                                    </div>
                                                                </div>
                                                            </div>


                                                        </div>

                                                        <button class="btn btn-block btn-success" type="submit">Save Default</button>

                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div> <!-- row end -->

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <x-footer />

        </div>
    </div>

</div>
