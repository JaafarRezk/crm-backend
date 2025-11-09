<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\FollowUp;
use App\Models\User;
use App\Policies\FollowUpPolicy;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        
        Gate::policy(FollowUp::class, FollowUpPolicy::class);
        Gate::policy(\App\Models\Client::class, \App\Policies\ClientPolicy::class);
        Gate::policy(User::class,UserPolicy::class);
    }
}


