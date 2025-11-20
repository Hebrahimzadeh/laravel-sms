<?php

namespace Omalizadeh\SMS\Drivers\Contracts;

use Omalizadeh\SMS\Requests\SendSMSRequest;
use Omalizadeh\SMS\Responses\SendSMSResponse;

abstract class Driver
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function getConfig(string $key)
    {
        return $this->config[$key] ?? null;
    }

    abstract public function send(SendSMSRequest $request): SendSMSResponse;

    abstract public function getSMSSendingURL(): string;
}
