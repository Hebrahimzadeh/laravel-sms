<?php

namespace Omalizadeh\Sms;

use Omalizadeh\Sms\Drivers\Contracts\Driver;
use Omalizadeh\Sms\Exceptions\DriverNotFoundException;
use Omalizadeh\Sms\Exceptions\InvalidConfigurationException;
use ReflectionClass;

class Sms
{
    protected array $driverConfig;
    protected string $providerName;
    protected Driver $driver;

    public function send()
    {

    }

    public function sendBulk()
    {

    }

    public function sendTemplate()
    {

    }

    public function setProvider(string $providerName): self
    {
        $this->setProviderName($providerName);
        $this->setDriverConfig();
        $this->setDriver();

        return $this;
    }

    public function getProviderName(): string
    {
        if (empty($this->providerName)) {
            $this->setDefaultDriver();
        }

        return $this->providerName;
    }

    protected function getDriver(): Driver
    {
        if (empty($this->driver)) {
            $this->setDefaultDriver();
        }

        return $this->driver;
    }

    private function setProviderName(string $providerName): void
    {
        $this->providerName = $providerName;
    }

    private function setDriverConfig(): void
    {
        $driverConfig = config($this->getProviderConfigKey());

        if (empty($driverConfig) || !is_array($driverConfig)) {
            throw new InvalidConfigurationException($this->getProviderName().' configurations not found');
        }

        $this->driverConfig = $driverConfig;
    }

    private function setDriver(): void
    {
        $class = config($this->getDriverNamespaceConfigKey());

        if (empty($class)) {
            throw new DriverNotFoundException($this->getProviderName().' driver class not defined');
        }

        if (!class_exists($class)) {
            throw new DriverNotFoundException($this->getProviderName().' driver class not found. try updating the package.');
        }

        $driver = new $class($this->driverConfig);

        if (!is_subclass_of($driver, Driver::class)) {
            throw new DriverNotFoundException($this->getProviderName().' driver class does not extend main driver class');
        }

        $this->driver = $driver;
    }

    private function getProviderConfigKey(): string
    {
        return 'sms.'.$this->getProviderName();
    }

    private function getDriverNamespaceConfigKey(): string
    {
        return $this->getProviderConfigKey().'.driver';
    }

    private function setDefaultDriver(): void
    {
        $defaultProviderName = config('sms.default_driver');

        if (empty($defaultProviderName) || !is_string($defaultProviderName)) {
            throw new InvalidConfigurationException('gateway not selected or default gateway does not exist.');
        }

        $this->setProvider($defaultProviderName);
    }

    private function validateDriverImplementsTargetInterface(string $interfaceName): void
    {
        $reflect = new ReflectionClass($this->getDriver());

        if (!$reflect->implementsInterface($interfaceName)) {
            throw new DriverNotFoundException("Driver does not implement $interfaceName.");
        }
    }
}
