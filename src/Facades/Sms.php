<?php

namespace Omalizadeh\Sms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Omalizadeh\Sms\SentSmsInfo send(string $phoneNumber, string $message, array $options = [])
 * @method static \Omalizadeh\Sms\SentSmsInfo sendTemplate(string $phoneNumber, $template, array $options = [])
 * @method static \Omalizadeh\Sms\BulkSentSmsInfo sendBulk(array $phoneNumbers, string $message, array $options = [])
 * @method static \Omalizadeh\Sms\Sms setProvider(string $providerName)
 * @method static string getProviderName()
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
