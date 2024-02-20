<?php

namespace App\Interfaces;


interface HomeRepositoryInterface
{
    
    public function index($user_id);
    public function checkPin($email, $pin);
    public function  getSubAccount($accountno);
}
