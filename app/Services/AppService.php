<?php

namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Models\Setting\ApplicationSetting;

class AppService  extends BaseAPIController
{
    public function processApp($type)
    {
        $service = ApplicationSetting::where("service_name", $type)->first();

        //if($service->status == "off"){
        if ($service && $service->status === 'off') {
            return $this->sendError("System Downtime", $type.'Service Unavailable, please try again later', Response::HTTP_BAD_REQUEST); 
        }

        return $service;

          
    }
}