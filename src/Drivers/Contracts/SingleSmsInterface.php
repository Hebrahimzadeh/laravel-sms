<?php

namespace Omalizadeh\Sms\Drivers\Contracts;

use Omalizadeh\Sms\SentSmsInfo;

interface SingleSmsInterface
{
    public function send(string $phoneNumber, string $message, array $options = []): SentSmsInfo;

    public function getSingleSmsUrl(): string;
}
