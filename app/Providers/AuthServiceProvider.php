<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Enums\RoleEnum;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate => Permission | Simple Role
        Gate::define(RoleEnum::super_admin()->value, function(User $user) : bool{
          return  $user->authority === RoleEnum::super_admin()->value;
        });

        Gate::define(RoleEnum::admin()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::admin()->value;
        });

        Gate::define(RoleEnum::agent()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::agent()->value;
        });

        Gate::define(RoleEnum::manager()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::manager()->value;
        });

        Gate::define(RoleEnum::supervisor()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::supervisor()->value;
        });

        Gate::define(RoleEnum::user()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::user()->value;
        });


        Gate::define(RoleEnum::payment_channel()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::payment_channel()->value;
        });

        Gate::define(RoleEnum::dtm()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::dtm()->value;
        });

         Gate::define(RoleEnum::bhm()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::bhm()->value;
        });

         Gate::define(RoleEnum::billing()->value, function(User $user) : bool{
            return  $user->authority === RoleEnum::billing()->value;
        });

        

        
    }
}
