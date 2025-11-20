<?php

namespace Omalizadeh\SMS\Facades;

use Illuminate\Support\Facades\Facade;
use Omalizadeh\SMS\Requests\SendBulkSMSRequest;
use Omalizadeh\SMS\Requests\SendSMSRequest;
use Omalizadeh\SMS\Responses\SendBulkSMSResponse;
use Omalizadeh\SMS\Responses\SendSMSResponse;

/**
 * @method static SendSMSResponse send(SendSMSRequest $request)
 * @method static SendSMSResponse sendTemplate(string $phoneNumber, $template, array $options = [])
 * @method static SendBulkSMSResponse sendBulk(SendBulkSMSRequest $request)
 * @method static \Omalizadeh\SMS\SMS setProvider(string $provider)
 * @method static string getProvider()
 *
 * @see \Omalizadeh\SMS\SMS
 */
class SMS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sms';
    }
}
