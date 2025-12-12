<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\KisApplication;
use App\Models\ClubDues;
use App\Models\PembalapProfile;
use App\Observers\UserObserver;
use App\Observers\ClubObserver;
use App\Observers\EventObserver;
use App\Observers\EventRegistrationObserver;
use App\Observers\KisApplicationObserver;
use App\Observers\ClubDuesObserver;
use App\Observers\PembalapProfileObserver;

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
        // Register Model Observers untuk Logging
        User::observe(UserObserver::class);
        Club::observe(ClubObserver::class);
        Event::observe(EventObserver::class);
        EventRegistration::observe(EventRegistrationObserver::class);
        KisApplication::observe(KisApplicationObserver::class);
        ClubDues::observe(ClubDuesObserver::class);
        PembalapProfile::observe(PembalapProfileObserver::class);
    }
}
