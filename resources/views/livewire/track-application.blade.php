<div  class="container-fluid page-body-wrapper">


    <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
        <div class="row flex-grow">
          <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="auth-form-transparent text-left p-3">
              <div class="brand-logo">
                <img src="https://www.ibedc.com/assets/img/logo.png" alt="logo">
              </div>
              <h4>Tracking Customer!</h4>
              <h6 class="font-weight-light">Welcome A Code will be sent to your email</h6>
              <form class="pt-3" wire:submit.prevent="submit">

                 <!-- Error message display -->
                 @if (session()->has('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="form-group">
                  <label for="tracking_id">Tracking ID</label>
                  <div class="input-group">
                    <div class="input-group-prepend bg-transparent">
                      <span class="input-group-text bg-transparent border-right-0">
                        <i class="mdi mdi-account-outline text-primary"></i>
                      </span>
                    </div>
                    <input type="text" wire:model="form.tracking_id" class="form-control form-control-lg border-left-0" id="tracking_id" placeholder="tracking id">
                    @error('form.tracking_id')<div  class="alert alert-danger">{{ $message}}</div> @enderror
                  </div>
                </div>
             
              
                <div class="my-3">
                  <button class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">TRACK</button>
                  <span wire:loading>Tracking Application....... </span><BR/>
                </div>
                
               
              </form>
            </div>
          </div>
          <div class="col-lg-6 login-half-bg d-flex flex-row">
            <p class="text-white font-weight-medium text-center flex-grow align-self-end">Copyright &copy; <?php echo date('Y'); ?>  All rights reserved.</p>
          </div>
        </div>
    </div>




</div>