<?php

namespace Omalizadeh\Sms\Drivers\Contracts;

interface BulkSmsInterface
{
    public function sendBulk(array $phoneNumbers, string $message, array $options = []);

    public function getBulkSmsUrl(): string;
}
