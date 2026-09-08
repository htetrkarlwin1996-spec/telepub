<?php

namespace App\Providers;

use App\Listeners\CreateUserDefaultsAfterRegistration;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            CreateUserDefaultsAfterRegistration::class,
        ],
    ];
}