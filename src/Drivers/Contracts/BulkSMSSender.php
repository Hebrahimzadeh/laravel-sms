<?php

namespace Omalizadeh\SMS\Drivers\Contracts;

use Omalizadeh\SMS\Requests\SendBulkSMSRequest;
use Omalizadeh\SMS\Responses\SendBulkSMSResponse;

interface BulkSMSSender
{
    public function sendBulk(SendBulkSMSRequest $request): SendBulkSMSResponse;

    public function getBulkSMSSendingURL(): string;
}
