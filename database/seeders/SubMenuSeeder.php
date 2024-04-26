<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubMenu;

class SubMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

     protected $array = [
        [
            "sub_menu_name" => "Customers",
            "sub_menu_url" => "all_customers",
            "menu_id" => "1"
        ],

        [
            "sub_menu_name" => "Outstanding Balance",
            "sub_menu_url" => "outstanding_balance",
            "menu_id" => "1"
        ],

        [
            "sub_menu_name" => "Application Settings",
            "sub_menu_url" => "application_settings",
            "menu_id" => "6"
        ],

        [
            "sub_menu_name" => "Create Role",
            "sub_menu_url" => "roles",
            "menu_id" => "6"
        ],

        [
            "sub_menu_name" => "Assign Role",
            "sub_menu_url" => "assign_role",
            "menu_id" => "6"
        ],


        [
            "sub_menu_name" => "API Keys",
            "sub_menu_url" => "api_keys",
            "menu_id" => "6"
        ],

        [
            "sub_menu_name" => "Complaints",
            "sub_menu_url" => "complaints",
            "menu_id" => "7"
        ],

        [
            "sub_menu_name" => "Pending Jobs",
            "sub_menu_url" => "pending_jobs",
            "menu_id" => "8"
        ],

        [
            "sub_menu_name" => "Failed Jobs",
            "sub_menu_url" => "failed_jobs",
            "menu_id" => "8"
        ],



    ];

    public function run(): void
    {
        foreach($this->array as $array) { SubMenu::create($array); }
    }
}
