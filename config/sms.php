<?php

return [
    'default_driver' => env('DEFAULT_SMS_PROVIDER', 'kavenegar'),

    'kavenegar' => [
        'driver_class' => \Omalizadeh\Sms\Drivers\Kavenegar\Kavenegar::class,
        'api_key' => '',
    ],
    'sms_ir' => [
        'driver_class' => \Omalizadeh\Sms\Drivers\SmsIr\SmsIr::class,
        'api_key' => '',
    ],
    'faraz_sms' => [
        'driver_class' => \Omalizadeh\Sms\Drivers\FarazSms\FarazSms::class,
        'api_key' => '',
        'default_sender' => env('FARAZ_DEFAULT_SENDER')
    ],
];
