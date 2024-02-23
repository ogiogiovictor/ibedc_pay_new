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
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VirtualAccount $event): void
    {
       // Log the event information
       Log::info('Virtual Account event triggered for user: ' . json_encode($event->user));

        $user = $event->user;
        (new CreateVitualAccountService)->createAccount($event->user);
    }
}
