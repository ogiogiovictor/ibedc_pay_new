<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MainMenu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

     protected $array = [

        [
            "menu_name" => "Customer",
            "menu_url" => "customer",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-view-array menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "All Transactions",
            "menu_url" => "transactions",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-drawing-box menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "User",
            "menu_url" => "users",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-bell menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Wallets",
            "menu_url" => "wallet_users",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-checkbox-marked-outline menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Audit Logs",
            "menu_url" => "syslog",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-emoticon-excited-outline menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Setting",
            "menu_url" => "syslog",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-emoticon-excited-outline menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Agency",
            "menu_url" => "agency",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-file-document menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Contact Us",
            "menu_url" => "contact_us",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-alert-circle menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Jobs",
            "menu_url" => "jobs",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-view-quilt menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "E-mail Customers",
            "menu_url" => "email_customers",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-email-outline menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Migration",
            "menu_url" => "migration",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-calendar-blank menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Documentation",
            "menu_url" => "documentation",
            "menu_status" =>  "on",
            "menu_icon" => "mdi mdi-file-document menu-icon",
            "menu_side" => "left"
        ],

        [
            "menu_name" => "Overview",
            "menu_url" => "dashboard",
            "menu_status" =>  "on",
            "menu_icon" => "",
            "menu_side" => "right"
        ],

        [
            "menu_name" => "Transactions",
            "menu_url" => "transactions",
            "menu_status" =>  "on",
            "menu_icon" => "",
            "menu_side" => "right"
        ],

        [
            "menu_name" => "Transactions -(v1)",
            "menu_url" => "log_transactions",
            "menu_status" =>  "on",
            "menu_icon" => "",
            "menu_side" => "right"
        ],

        [
            "menu_name" => "Users",
            "menu_url" => "users",
            "menu_status" =>  "on",
            "menu_icon" => "",
            "menu_side" => "right"
        ],

        [
            "menu_name" => "System Logs",
            "menu_url" => "syslog",
            "menu_status" =>  "on",
            "menu_icon" => "",
            "menu_side" => "right"
        ],
       
       
    ];

    public function run(): void
    {
       
            foreach($this->array as $array) { MainMenu::create($array); }
    
    }
}
