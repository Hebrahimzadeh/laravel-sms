<?php

namespace Omalizadeh\Sms\Providers;

use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/sms.php',
            'sms.php'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/multipayment'),
                __DIR__.'/../../config' => config_path(),
            ]);
        }
    }
}
