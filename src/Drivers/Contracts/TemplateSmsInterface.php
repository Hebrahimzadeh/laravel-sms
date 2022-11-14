<?php

namespace Omalizadeh\Sms\Drivers\Contracts;

interface TemplateSmsInterface
{
    public function sendTemplate(string $phoneNumber, $template, array $options = []);

    public function getTemplateSmsUrl(): string;
}
