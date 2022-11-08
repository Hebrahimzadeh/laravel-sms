<?php

namespace Omalizadeh\Sms\Tests;

use Omalizadeh\Sms\Providers\SmsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SmsServiceProvider::class
        ];
    }
}
