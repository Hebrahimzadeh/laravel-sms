<?php

namespace Omalizadeh\SMS\Tests;

use Omalizadeh\SMS\Providers\SMSServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SMSServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $smsConfig = require __DIR__ . '/../config/sms.php';

        $app['config']->set('sms', $smsConfig);
        $app['config']->set('sms.kavenegar.api_key', 'xx-testing-api-key-xx');
    }
}
