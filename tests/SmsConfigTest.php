<?php

namespace Omalizadeh\Sms\Tests;

use Omalizadeh\Sms\Exceptions\InvalidConfigurationException;
use Omalizadeh\Sms\Facades\Sms;

class SmsConfigTest extends TestCase
{
    public function testDefaultProviderIsIdentified(): void
    {
        $this->assertEquals('kavenegar', Sms::getProviderName());
    }

    public function testErrorOnUndefinedSmsProvider(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        Sms::setProvider('undefined_provider');
    }
}
