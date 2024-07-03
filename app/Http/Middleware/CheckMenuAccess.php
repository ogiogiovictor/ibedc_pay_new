<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\MainMenu;
use App\Models\MenuAccess;
use App\Models\SubMenu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the route name
        $routeName = $request->route()->getName();

        // Get the additional parameters from the route
        $parameters = $request->route()->parameters();

        // Extract the menu name from the route name
        $menuName = $this->extractMenuName($routeName);

        $menuID = MainMenu::where("menu_url", $menuName)->first();

        // Get the authenticated user
        $user = Auth::user();

        // Get the user's roles
        // $user->roles->first()->id;
        $rolesID = $user->roles->first()->id;

        //Check the user role and return the menu
        $usermenuID = MenuAccess::where("user_role", $rolesID)->first();

        // dd(explode(",", $usermenuID->menu_id));
        // dd($menuID->id);
       // dd($menuID->menu_side);

        if(isset($menuID->menu_side) && $menuID->menu_side  ==  "left"){
            if(in_array($menuID->id, explode(",", $usermenuID->menu_id))){
                 return $next($request);
             } else {
                 abort(403, 'Unathorized action.');
             }
        }

        if(isset($menuID->menu_side) && $menuID->menu_side  ==  "right"){
            return $next($request);
        }

        $subMenu = SubMenu::where("sub_menu_url", $routeName)->first();

       // dd($routeName);
       // dd($subMenu);

        
        if(!$menuID && $parameters){ // i will need to add the url with id in the database and call it inner
          //  dd($routeName);


            //Get the previous menu where it was coming from 
            $previousMenuName = $request->headers->get('referer');
          
            $getName = str_replace("https://ipay.ibedc.com:7642/", '', $previousMenuName);
            
            $menuID = MainMenu::where("menu_url", $getName)->first();
          //check the submenu
          $subMenu = SubMenu::where("sub_menu_url", $routeName)->first();

         // dd($subMenu);

          if(isset($subMenu) && $subMenu){
            if(in_array($subMenu->menu_id, explode(",", $usermenuID->menu_id))){
                return $next($request);
            } 
          } else if(isset($menuID->menu_side) && $menuID->menu_side  ==  "left"){
                if(in_array($menuID->id, explode(",", $usermenuID->menu_id))){
                    return $next($request);
                } 
            } else {
                //we are not done yet we need to do an else if to check for inner menu
                return $next($request);
                abort(403, 'Unathorized action. No Access Resource'); 
            }

        } 


        //Checking for the Submenu
        if($subMenu){
            if(in_array($subMenu->menu_id, explode(",", $usermenuID->menu_id))){
                return $next($request);
            } else {
                abort(403, 'Unathorized action. Resource Not Available'); 
            }
        }

        //Checking for the buttons in the menu like view/transactionid = /view_acess_log/1

        if(!$subMenu){
            return $next($request);
        }

       // you need to also check if $usermenuID is not a main menu and find another login 

        
    }

     // Function to extract menu name from route name
     private function extractMenuName($routeName)
     {
         // Split route name by '.'
         $parts = explode('.', $routeName);
 
         // The last part is the menu name
         return end($parts);
     }
}
