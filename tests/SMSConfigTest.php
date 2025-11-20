<?php

namespace Omalizadeh\SMS\Tests;

use Omalizadeh\SMS\Exceptions\InvalidSMSConfigurationException;
use Omalizadeh\SMS\Facades\SMS;

class SMSConfigTest extends TestCase
{
    public function testDefaultProviderIsIdentified(): void
    {
        $this->assertEquals('kavenegar', SMS::getProvider());
    }

    public function testErrorOnUndefinedSMSProvider(): void
    {
        $this->expectException(InvalidSMSConfigurationException::class);

        SMS::setProvider('undefined_provider');
    }
}
