<?php

namespace Omalizadeh\Sms\Drivers\Contracts;

use Omalizadeh\Sms\BulkSentSmsInfo;

interface BulkSmsInterface
{
    public function sendBulk(array $phoneNumbers, string $message, array $options = []): BulkSentSmsInfo;

    public function getBulkSmsUrl(): string;
}
