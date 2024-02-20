<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Logs\AppAuthorization;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorize = AppAuthorization::create([
            'appSecret' => "SK_161719MDUKDCMEU_45@MUDCaPP0921SDK_VSION11",
            'appToken' => "TK_161719MDUKDCMEU_45@MUDCaPP0921SDK_TK190MD",
            'appName' => "IBEDCPay Version2",
            'status' => 1,
            'ip_address' => "127.0.0.1"
        ]);

        
    }
}
