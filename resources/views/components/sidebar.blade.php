<div>
<?php
        $user = auth()->user();
        $role_id = $user->roles->first()->id;
        $menus = [];
        
        //$main_menu = \App\Models\MainMenu::where("menu_side", "left")->get();

    $main_menu2 = \App\Models\MenuAccess::where("user_role", $role_id)->first();

    $main_menu = explode(",",  $main_menu2->menu_id);

    //foreach ($main_menu as $mainMenu) {
    foreach ($main_menu as $menuID) {
     $imenu = \App\Models\MainMenu::where(["menu_side" => "left", "id" => $menuID])->first();

     $menu = [
         'id' => $imenu->id,
         'name' => $imenu->menu_name,
         'status' => $imenu->menu_status,
         'menu_url' => $imenu->menu_url,
         'icon' => $imenu->menu_icon,
         'submenus' => []
     ];

     //$submenus = \App\Models\SubMenu::where("menu_id", $mainMenu->id)->get();
     $submenus = \App\Models\SubMenu::where("menu_id", $menuID)->get();

     foreach ($submenus as $submenu) {
         $menu['submenus'][] = [
             'id' => $submenu->id,
             'name' => $submenu->sub_menu_name,
             'url' => $submenu->sub_menu_url,
             // Add other properties you need from the submenu model
         ];

     }

     $menus[] = $menu;

    
    
 }
?>

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
                 {{ $user->name }}
                </p>
                <p class="designation">
                  {{ strtoupper(str_replace('_', ' ', $user->authority)) }}
                </p>
              </div>
            </div>
          </li>

        @if($user->authority == 'dtm' || $user->authority == 'billing' || $user->authority == 'bhm' || $user->authority == 'mso'  || $user->authority == 'rico')
            <li class="nav-item"> </li>
        @else
            <li class="nav-item">
                <a class="nav-link" href="/dashboard" wire:navigate>
                    <i class="mdi mdi-shield-check menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>
        @endif
          <!-- <li class="nav-item">
            <a class="nav-link"  href="/dashboard"  wire:navigate>
            <i class="mdi mdi-shield-check menu-icon"></i>
            <span class="menu-title">Dashboard</span>
            </a>
          </li> -->



           <!-- Main Menu -->
           @foreach($menus as $menu)
           <li class="nav-item">
            <!-- <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic"> -->
            <a class="nav-link" @if (!empty($menu['submenus'])) data-toggle="collapse" @endif 
            @if (!empty($menu['submenus'])) href="#{{ $menu['name'] }}" @else href="/{{ $menu['menu_url'] }}" @endif 
            aria-expanded="false" aria-controls="{{ $menu['name'] }}">
            
            <i class="{{ $menu['icon'] }}"></i>
            <span class="menu-title">{{ $menu['name'] }}</span>
            @if (!empty($menu['submenus']))<i class="menu-arrow"></i>@endif 
            </a>
            @if(!empty($menu['submenus']))
              @foreach($menu['submenus'] as $sub)
              <div class="collapse" id="{{ $menu['name'] }}">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link"  href="/{{ $sub['url'] }}"  wire:navigate>{{ $sub['name'] }}</a></li>
                </ul>
              </div>
              @endforeach
            @endif
          </li>
          @endforeach
            <!-- End Main Menu -->

            <!-- <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#maps" aria-expanded="false" aria-controls="maps">
            <i class="mdi mdi-map menu-icon"></i>
            <span class="menu-title">Setting</span>
            <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="maps">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link" href="/roles">Create Role</a></li>
                <li class="nav-item"> <a class="nav-link" href="/assign_role">Access Control List</a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/maps/vector-map.html">App Setting</a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/maps/vector-map.html">API Keys</a></li>
              </ul>
            </div>
          </li> -->
           

         
          <!-- <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
            <i class="mdi mdi-view-array menu-icon"></i>
            <span class="menu-title">Customers</span>
            <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="ui-basic">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"> <a class="nav-link"  href="/customers"  wire:navigate>All Customers</a></li>
                <li class="nav-item"> <a class="nav-link" href="/outstanding_balances">Oustanding Balances</a></li>
              </ul>
            </div>
          </li> -->
         


          <!-- <li class="nav-item">
            <a class="nav-link" href="/transactions" wire:navigate>
            <i class="mdi mdi-drawing-box menu-icon"></i>
            <span class="menu-title">All Transaction</span>
            </a>
          </li> -->

          <!-- <li class="nav-item">
            <a class="nav-link"  href="/users"  wire:navigate>
            <i class="mdi mdi-bell menu-icon"></i>
            <span class="menu-title">Users</span>
            </a>
          </li> -->

          <!-- <li class="nav-item">
            <a class="nav-link" href="/wallet_users"  wire:navigate>
            <i class="mdi mdi-checkbox-marked-outline menu-icon"></i>
            <span class="menu-title">Wallets</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="/syslog" wire:navigate>
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
                <li class="nav-item"> <a class="nav-link" href="/roles">Create Role</a></li>
                <li class="nav-item"> <a class="nav-link" href="/assign_role">Access Control List</a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/maps/vector-map.html">App Setting</a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/maps/vector-map.html">API Keys</a></li>
              </ul>
            </div>
          </li> -->
          
          <!-- <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="/contact" aria-expanded="false" aria-controls="error">
            <i class="mdi mdi-alert-circle menu-icon"></i>
            <span class="menu-title">Contact Us</span>
            </a>
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
          </li> -->
         
          <!-- <li class="nav-item">
            <a class="nav-link" href="pages/apps/gallery.html">
            <i class="mdi mdi-image-filter menu-icon"></i>
            <span class="menu-title">Gallery</span>
            </a>
          </li> -->
          <!-- <li class="nav-item">
            <a class="nav-link" href="pages/documentation/documentation.html">
            <i class="mdi mdi-file-document menu-icon"></i>
            <span class="menu-title">Documentation</span>
            </a>
          </li> -->
        </ul>
      </nav>
</div>