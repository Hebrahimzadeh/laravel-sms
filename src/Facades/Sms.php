<?php

namespace Omalizadeh\Sms\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array send(string $phoneNumber, string $message, array $options = [])
 * @method static array sendBulk(array $phoneNumbers, string $message, array $options = [])
 * @method static array sendTemplate(string $phoneNumber, $template, array $options = [])
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
