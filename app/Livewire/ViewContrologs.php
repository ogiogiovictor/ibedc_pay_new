<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Models\MenuAccess;

class ViewContrologs extends Component
{
    public $id = [];
    public $submenu = [];
    public $menus = [];


    public function mount() {

        $mainMenus = MainMenu::where("menu_side", "left")->get();

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
        dd($this->menus);
    }

    public function render()
    {
        return view('livewire.view-contrologs', [
            'menus' => $this->menus,
        ]);
       // return view('livewire.view-contrologs');
    }
}
