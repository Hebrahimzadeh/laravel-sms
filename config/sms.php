<?php

return [
    'default_driver' => 'kavenegar',

    'kavenegar' => [
        'driver_class' => \Omalizadeh\Sms\Drivers\Kavenegar\Kavenegar::class,
        'api_key' => '',
    ],
    'sms_ir' => [
        'driver_class' => '',
    ],
];
