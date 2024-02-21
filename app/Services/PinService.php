<?php

namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\BaseAPIController;
use App\Jobs\PinJob; // This will be pin Job

class PinService
{
    public function processPin($email)
    {
        $user = User::where('email', $email)->first();

        $pin = strval(rand(100000, 999999));
        $user->update(['pin' => $pin]);

        //dispatch a pin email to the user
        dispatch(new PinJob($user));

        return $user;
    }
}
