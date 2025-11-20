<?php

namespace Omalizadeh\SMS\Drivers\Contracts;

use Omalizadeh\SMS\Requests\SendTemplateSMSRequest;
use Omalizadeh\SMS\Responses\SendSMSResponse;

interface TemplateSMSSender
{
    public function sendTemplate(SendTemplateSMSRequest $request): SendSMSResponse;

    public function getTemplateSMSSendingURL(): string;
}
