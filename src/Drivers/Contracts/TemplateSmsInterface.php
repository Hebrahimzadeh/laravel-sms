<?php

namespace Omalizadeh\Sms\Drivers\Contracts;

use Omalizadeh\Sms\SentSmsInfo;

interface TemplateSmsInterface
{
    public function sendTemplate(string $phoneNumber, $template, array $options = []): SentSmsInfo;

    public function getTemplateSmsUrl(): string;
}
