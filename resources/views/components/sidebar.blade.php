<div>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item nav-profile">
            <div class="nav-link d-flex">
              <div class="profile-image">
                <!-- <img src="https://via.placeholder.com/37x37" alt="image"> -->
                <img  src="https://www.ibedc.com/assets/img/logo.png" alt="image">
              </div>
              <div class="profile-name">
                <p class="name">
                  Welcome Victor
                </p>
                <p class="designation">
                  Administrator
                </p>
              </div>
            </div>
          </li>
          <li class="nav-item">
            <a class="nav-link"  href="/dashboard"  wire:navigate>
            <i class="mdi mdi-shield-check menu-icon"></i>
            <span class="menu-title">Dashboard</span>
            </a>
          </li>
         
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
            <i class="mdi mdi-view-array menu-icon"></i>
            <span class="menu-title">Customers</span>
            <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link"  href="/customers"  wire:navigate>All Customers</a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/ui-features/buttons.html">Oustanding Balances</a></li>
              </ul>
            </div>
          </li>
         


          <li class="nav-item">
            <a class="nav-link" href="/transactions" wire:navigate>
            <i class="mdi mdi-drawing-box menu-icon"></i>
            <span class="menu-title">All Transaction</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link"  href="/users"  wire:navigate>
            <i class="mdi mdi-bell menu-icon"></i>
            <span class="menu-title">Users</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="pages/apps/todo.html">
            <i class="mdi mdi-checkbox-marked-outline menu-icon"></i>
            <span class="menu-title">Wallets</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="pages/ui-features/notifications.html">
            <i class="mdi mdi-emoticon-excited-outline menu-icon"></i>
            <span class="menu-title">Audit Logs</span>
            </a>
          </li>


         
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#maps" aria-expanded="false" aria-controls="maps">
            <i class="mdi mdi-map menu-icon"></i>
            <span class="menu-title">Setting</span>
            <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="maps">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="pages/maps/mapael.html">App Setting</a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/maps/vector-map.html">API Keys</a></li>
              </ul>
            </div>
          </li>
          
          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#error" aria-expanded="false" aria-controls="error">
            <i class="mdi mdi-alert-circle menu-icon"></i>
            <span class="menu-title">Contact Us</span>
            <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="error">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="pages/samples/error-404.html"> Complains </a></li>
              </ul>
            </div>
          </li>


          <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#general-pages" aria-expanded="false" aria-controls="general-pages">
            <i class="mdi mdi-view-quilt menu-icon"></i>
            <span class="menu-title">Jobs</span>
            <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="general-pages">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="pages/samples/blank-page.html"> Pending Jobs </a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/samples/profile.html"> Failed Jobs </a></li>
              </ul>
            </div>
          </li>
         
          <li class="nav-item">
            <a class="nav-link" href="pages/apps/email.html">
            <i class="mdi mdi-email-outline menu-icon"></i>
            <span class="menu-title">E-mail Customers</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="pages/apps/calendar.html">
            <i class="mdi mdi-calendar-blank menu-icon"></i>
            <span class="menu-title">Migrations</span>
            </a>
          </li>
         
          <!-- <li class="nav-item">
            <a class="nav-link" href="pages/apps/gallery.html">
            <i class="mdi mdi-image-filter menu-icon"></i>
            <span class="menu-title">Gallery</span>
            </a>
          </li> -->
          <li class="nav-item">
            <a class="nav-link" href="pages/documentation/documentation.html">
            <i class="mdi mdi-file-document menu-icon"></i>
            <span class="menu-title">Documentation</span>
            </a>
          </li>
        </ul>
      </nav>
</div>