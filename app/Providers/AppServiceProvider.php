<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Interfaces\HomeRepositoryInterface;
use App\Repositories\HomeRepository;
use App\Interfaces\TransactionRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Gate;
use App\Models\User;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(HomeRepositoryInterface::class, HomeRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }

    /**
     * Bootstrap any application services.  //RoleEnum::agent()->value
     */
    public function boot(): void
    {
       // $this->registerpolicies();

        Schema::defaultStringLength(191);

        Gate::define('isAdmin', function(User $user) {
            return $user->authority == RoleEnum::admin()->value;
        });

        Gate::define('isCustomer', function(User $user) {
            return $user->authority == RoleEnum::customer()->value;
        });

        Gate::define('isAgent', function(User $user) {
            return $user->authority == RoleEnum::agent()->value;
        });

        Gate::define('isUser', function(User $user) {
            return $user->authority == RoleEnum::user()->value;
        });

        Gate::define('isManager', function(User $user) {
            return $user->authority == RoleEnum::manager()->value;
        });

        Gate::define('isSupervisor', function(User $user) {
            return $user->authority == RoleEnum::supervisor()->value;
        });
    }
}
