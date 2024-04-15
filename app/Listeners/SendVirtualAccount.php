<?php

namespace App\Listeners;

use App\Events\VirtualAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\CreateVitualAccountService;
use Illuminate\Support\Facades\Log;

class SendVirtualAccount implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        Log::info('SendVirtualAccount listener instantiated');
    }

    /**
     * Handle the event.
     */
    public function handle(VirtualAccount $event): void
    {
        $user = $event->user;
       // (new CreateVitualAccountService)->createAccount($event->user); // uncomment when you want to go to live
    }
}
