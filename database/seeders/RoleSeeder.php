<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Enums\RoleEnum;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    protected $array = [

        [
            "name" => "super_admin",
            "guard_name" => "web"
        ],

        [
            "name" => "admin",
            "guard_name" => "web"
        ],

        [
            "name" => "manager",
            "guard_name" => "web"
        ],

        [
            "name" => "supervisor",
            "guard_name" => "web"
        ],

        [
            "name" => "customer",  //agent
            "guard_name" => "web"
        ],

        [
            "name" => "agent",  
            "guard_name" => "web"
        ],

        [
            "name" => "payment_channel",  
            "guard_name" => "web"
        ],

        //payment_channel

    ];

    public function run(): void
    {
        foreach($this->array as $array) { Role::create($array); }
    }
}
