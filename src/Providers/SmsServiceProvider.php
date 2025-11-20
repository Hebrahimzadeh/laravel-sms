<?php

namespace Omalizadeh\SMS\Providers;

use Illuminate\Support\ServiceProvider;
use Omalizadeh\SMS\SMS;

class SMSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('SMS', function () {
            return new SMS();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/sms.php',
            'sms',
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/sms.php' => config_path('sms.php'),
            ]);
        }
    }
}
