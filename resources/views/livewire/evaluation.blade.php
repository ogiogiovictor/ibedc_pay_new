<div class="container-fluid page-body-wrapper">
    <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
        <div class="row flex-grow w-100">

            {{-- LEFT SIDE: Tracking Details & Form --}}
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white">
                <div class="auth-form-transparent text-left p-4 w-100" style="max-width: 600px;">
                    <div class="text-center mb-4">
                        <img src="https://www.ibedc.com/assets/img/logo.png" alt="IBEDC Logo" style="height: 60px;">
                    </div>

                    <h2 class="text-primary font-weight-bold mb-4">Technical Evaluation</h2>

                    @if (isset($tracking))
                        <ul class="list-unstyled mb-4">
                            <li><strong>Tracking ID:</strong> {{ $tracking->tracking_id }}</li>
                            <li><strong>Surname:</strong> {{ $tracking->surname }}</li>
                            <li><strong>Firstname:</strong> {{ $tracking->firstname }}</li>
                            <li><strong>Other Name:</strong> {{ $tracking->other_name }}</li>
                            <li><strong>House No:</strong> {{ $tracking->house_no }}</li>
                            <li><strong>Address:</strong> {{ $tracking->address }}</li>
                            <li><strong>Lat/Long:</strong> {{ $tracking->continuation->latitude }} | {{ $tracking->continuation->longitude }}</li>
                            <li><strong>Accounts Requested:</strong> {{ $tracking->no_of_account_apply_for }}</li>
                            <li><strong>Status:</strong> {{ ucfirst($tracking->status) }}</li>
                        </ul>
                    @endif

                    {{-- Error message --}}
                    @if (session()->has('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form wire:submit.prevent="submit">

                        <div class="form-group">
                            <label for="validation_code">Are the Service Cables visible from termination point on the pole to the customers fuse board/box?</label>
                            <select class="form-control">
                                <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                            @error('validationcode')<div class="text-danger">{{ $message }}</div>@enderror
                        </div>

                         <div class="form-group">
                            <label for="validation_code">Are the Service Cables passing through the Ceiling</label>
                            <select class="form-control">
                                <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Is the Metering Point at the entrance of the premises?</label>
                            <select class="form-control">
                                <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Does the quality of the Service Cables meet IBEDC standard?</label>
                            <select class="form-control">
                                <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Does the quality of the Service Cables meet IBEDC standard?</label>
                            <select class="form-control">
                                <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Are there any extensions of Service Cables from the concerned premise/apartment to other buildings/apartments?</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                 <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Is there significant room for expansion of energy consumption?</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                 <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">If Single Phase Meter is recommended, have the other phases been disconnected and removed from the premises?</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                 <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                         <div class="form-group">
                            <label for="validation_code">Is there an existing Meter?</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                 <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                         <div class="form-group">
                            <label for="validation_code">Is premises fit for Metering?</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                 <option value="Yes">Yes</option>
                                 <option value="No">No</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Enter Existing Meter Number (If any)</label>
                            <input type="text" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="validation_code">How many Phases enters the Customer's Premises</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                 <option value="One Phase">One Phase</option>
                                 <option value="Two Phase">Two Phase</option>
                                 <option value="Three Phase">Three Phase</option>
                            </select>
                        </div>


                         <div class="form-group">
                            <label for="validation_code">Where are the Extension Service Cables terminated</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                <option value="Fuse Board/Box of Applicant">Fuse Board/Box of Applicant</option>
                                 <option value="Connected along main Service Cables">Connected along main Service Cables</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Service Cable Description (If Service Cable is not visible)</label>
                            <input type="text" class="form-control" />
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Current energy consumption*</label>
                            <input type="text" class="form-control" />
                        </div>

                         <div class="form-group">
                            <label for="validation_code">Type of Meter recommended</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                <option value="Single Phase Meter">Single Phase Meter</option>
                                 <option value="Three Phase Meter">Three Phase Meter</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="validation_code">Recommended Tariff</label>
                            <select class="form-control">
                                <option value="">Select</option>
                                <option value="A1">A1</option>
                                 <option value="C1">C1</option>
                                  <option value="D1">D1</option>
                                 <option value="R2">R2</option>
                            </select>
                        </div>


                        <!-- Add Upload File Here -->



                       

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-block">Submit</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT SIDE: Branding or Background --}}
            <div class="col-lg-6 d-flex flex-column justify-content-center align-items-center bg-primary text-white p-4">
                <div class="text-center">
                    <h1 class="font-weight-bold">IBEDC</h1>
                    <p class="lead">Customer Account Tracking Portal</p>
                    <p class="mt-5">&copy; {{ date('Y') }} All rights reserved.</p>
                </div>
            </div>

        </div>
    </div>
</div>