<?php

namespace Omalizadeh\Sms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Omalizadeh\Sms\SentSmsInfo send(string $phoneNumber, string $message, array $options = [])
 * @method static array sendBulk(array $phoneNumbers, string $message, array $options = [])
 * @method static array sendTemplate(string $phoneNumber, $template, array $options = [])
 * @method static string getProviderName()
 * @method static \Omalizadeh\Sms\Sms setProvider(string $providerName)
 *
 * @see \Omalizadeh\Sms\Sms
 *
 */
class Sms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Omalizadeh\Sms\Sms::class;
    }
}
