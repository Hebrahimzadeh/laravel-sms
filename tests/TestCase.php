<?php

namespace Omalizadeh\Sms\Tests;

use Omalizadeh\Sms\Drivers\Kavenegar\Kavenegar;
use Omalizadeh\Sms\Providers\SmsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SmsServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $smsConfig = require __DIR__.'../../config/sms.php';

        $app['config']->set('sms', $smsConfig);
        $app['config']->set('sms.kavenegar.api_key', 'xx-testing-api-key-xx');
    }
}
