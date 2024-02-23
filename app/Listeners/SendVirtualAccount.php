<?php

namespace App\Listeners;

use App\Events\VirtualAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\CreateVitualAccountService;

class SendVirtualAccount implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VirtualAccount $event): void
    {
        $user = $event->user;
        (new CreateVitualAccountService)->createAccount($user);
    }
}
