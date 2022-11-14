<?php

namespace Omalizadeh\Sms\Drivers\Contracts;

abstract class Driver implements SingleSmsInterface
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function hasConfig(string $key): bool
    {
        return isset($this->config[$key]);
    }

    protected function getConfig(?string $key = null)
    {
        if (is_null($key)) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    abstract public function send(string $phoneNumber, string $message, array $options = []);

    abstract public function getSingleSmsUrl(): string;
}
