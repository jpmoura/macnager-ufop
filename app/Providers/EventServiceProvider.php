<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LogSuccessfulLogin',
        ],
        'Illuminate\Auth\Events\Logout' => [
            'App\Listeners\LogSuccessfulLogout',
        ],
        'App\Events\LoginFailed' => [
            'App\Listeners\LogFailedLogin',
        ],
        'App\Events\RequestStored' => [
            'App\Listeners\LogRequestStored',
        ],
        'App\Events\RequestApproved' => [
            'App\Listeners\LogRequestApproved',
        ],
        'App\Events\RequestDenied' => [
            'App\Listeners\LogRequestDenied',
        ],
        'App\Events\RequestExcluded' => [
            'App\Listeners\LogRequestExcluded',
        ],
        'App\Events\RequestReactivated' => [
            'App\Listeners\LogRequestReactivated',
        ],
        'App\Events\RequestSuspended' => [
            'App\Listeners\LogRequestSuspended',
        ],
        'App\Events\DeviceStored' => [
            'App\Listeners\LogDeviceStored',
        ],
        'App\Events\DeviceEdited' => [
            'App\Listeners\LogDeviceEdited',
        ],
        'App\Events\RequestExpired' => [
            'App\Listeners\LogRequestExpired'
        ],
        'App\Events\LdapiErrorOnLogin' => [
            'App\Listeners\LogLdapiErrorOnLogin'
        ],
        'App\Events\LdapiErrorOnSearch' => [
            'App\Listeners\LogLdapiErrorOnSearch'
        ],
        'App\Events\NewUserCreated' => [
            'App\Listeners\LogNewUserCreated'
        ],
        'App\Events\NewConfigurationFile' => [
            'App\Events\LogNewConfigurationFile',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
