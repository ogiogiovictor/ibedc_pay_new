<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\User;
use App\Observers\RegisterObserver;
use App\Observers\AccountOberver;
use App\Events\VirtualAccount;
use App\Listeners\SendVirtualAccount;
use App\Models\NAC\AccoutCreaction;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\ContactUs' => [
            'App\Listeners\SendContactUsEmail'
        ],
        VirtualAccount::class => [
            SendVirtualAccount::class,
        ]

    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        User::observe(RegisterObserver::class);
        AccoutCreaction::observe(AccountOberver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
