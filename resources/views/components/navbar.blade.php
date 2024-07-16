<div>
<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-left navbar-brand-wrapper d-flex align-items-center justify-content-between">
        <a class="navbar-brand brand-logo" href="index.html"><span style="color:white; font-size:bolder">IBEDC PAY</span></a>
        <a class="navbar-brand brand-logo-mini" href="index.html"><img src="https://www.ibedc.com/assets/img/logo.png" alt="logo"/></a> 
        <button class="navbar-toggler align-self-center" type="button" data-toggle="minimize">
        <span class="mdi mdi-menu"></span>
        </button>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <ul class="navbar-nav">
          <li class="nav-item  dropdown d-none align-items-center d-lg-flex d-none">
            <a class="dropdown-toggle btn btn-outline-secondary btn-fw"  href="#" data-toggle="dropdown" id="pagesDropdown">
            <span class="nav-profile-name">Settings</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="pagesDropdown">
              <a class="dropdown-item">
              <i class="mdi mdi-settings text-primary"></i>
              Profile
              </a>
              
              <a class="dropdown-item" wire:click.prevent="logout">
              <i class="mdi mdi-logout text-primary"></i>
              Logout
              </a>
            </div>
          </li>
         
        </ul>
        <ul class="navbar-nav navbar-nav-right">
            <!-- <li class="nav-item nav-search d-none d-lg-flex">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="search">
                  <i class="mdi mdi-magnify"></i>
                  </span>
                </div>
                <input type="text" class="form-control" placeholder="Type to search..." aria-label="search" aria-describedby="search">
              </div>
            </li> -->
           
          
            <li class="nav-item nav-user-icon">
              <a class="nav-link" href="#">
              <img src="https://via.placeholder.com/37x37" alt="profile"/>
              </a>
            </li>

            <li class="nav-item nav-settings d-none d-lg-flex">
              <a class="nav-link" href="#" wire:click.prevent="logout" placeholder="Logout">
              <i class="mdi mdi-power"></i>
              </a>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
        <span class="mdi mdi-menu"></span>
        </button>
      </div>
    </nav>
</div>