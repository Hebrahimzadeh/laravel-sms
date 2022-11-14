<?php

namespace Omalizadeh\Sms\Drivers\Contracts;

interface SingleSmsInterface
{
    public function send(string $phoneNumber, string $message, array $options = []);

    public function getSingleSmsUrl(): string;
}
