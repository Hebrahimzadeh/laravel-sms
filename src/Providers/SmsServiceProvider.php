<?php

namespace Omalizadeh\Sms\Providers;

use Illuminate\Support\ServiceProvider;
use Omalizadeh\Sms\Sms;

class SmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('SmsProvider', function () {
            return new Sms();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../../config/sms.php',
            'sms.php'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/sms.php' => config_path('sms.php'),
            ]);
        }
    }
}
