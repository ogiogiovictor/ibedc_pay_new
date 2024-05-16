<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Models\MenuAccess;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Livewire\AuthorizeTransactions;


class ViewContrologs extends Component
{
    public $id = [];
    public $submenu = [];
    public $menus = [];
    public $roleName;
    public $role_name;
    public $menu_name = [];
    public $role_access;
    public $user;
    public $user_role;
    public $roleIds;
    public $access;



    public function mount() {


        

        $this->user = Auth::user();

        $this->acesss = AuthorizeTransactions::authorizeTransaction($this->user);

         $this->user_role =  $this->user->getRoleNames();
         $this->roleIds =  $this->user->roles()->pluck('id');
        
        $this->roleName = Role::where("id", $this->id)->first();
        $mainMenus = MainMenu::where("menu_side", "left")->get();

        $this->access = MenuAccess::where("user_role", $this->id)->first();

        //dd($this->access->menu_id);

        foreach ($mainMenus as $mainMenu) {
            $menu = [
              'id' => $mainMenu->id,
              'name' => $mainMenu->menu_name,
              'status' => $mainMenu->menu_status,
              'icon' => $mainMenu->menu_icon,
              'submenus' => [],
            ];
          
            $menu['submenus'] = SubMenu::where('menu_id', $mainMenu->id)->get()
              ->map(function ($submenu) {
                return [
                  'id' => $submenu->id,
                  'name' => $submenu->sub_menu_name,
                  'url' => $submenu->sub_menu_url,
                  // Add other properties you need from the submenu model
                ];
              })
              ->toArray();
          
            $this->menus[] = $menu;

        // foreach ($mainMenus as $mainMenu) {

        //     $menu = [
        //         'id' => $mainMenu->id,
        //         'name' => $mainMenu->menu_name,
        //         'status' => $mainMenu->menu_status,
        //         'icon' => $mainMenu->menu_icon,
        //         'submenus' => []
        //     ];

        //     $submenus = SubMenu::where("menu_id", $mainMenu->id)->get();

        //     foreach ($submenus as $submenu) {
        //         $menu['submenus'][] = [
        //             'id' => $submenu->id,
        //             'name' => $submenu->sub_menu_name,
        //             'url' => $submenu->sub_menu_url,
        //             // Add other properties you need from the submenu model
        //         ];
        //     }

        //     $this->menus[] = $menu;
        // }
        }
        //dd($this->menus);
    }

    public function assignMenu() {

        // dd($this->menu_name);
       // dd($this->role_name);

        if(empty($this->menu_name) || !$this->role_name){
            Session::flash('error', 'Please select menu and select the role');
            return;
        }

      
        // Convert menu_name array to a comma-separated string
        $menuIds = implode(",", $this->menu_name);

        // Update or insert menu access based on the role_name
        $updateInsert = MenuAccess::updateOrCreate(
            ['user_role' => $this->role_name],
            ['menu_id' => $menuIds]
        );


        Session::flash('success', "Menu successful");
       
    }

    public function render()
    {
        return view('livewire.view-contrologs', [
            'menus' => $this->menus,
            'role' => $this->roleName,
            'user_menu_access' => $this->access->menu_id,
            //'all_role' => Role::all()
        ]);


       // return view('livewire.view-contrologs');
    }
}
