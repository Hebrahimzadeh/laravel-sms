<?php

namespace Omalizadeh\SMS;

use Omalizadeh\SMS\Drivers\Contracts\BulkSMSSender;
use Omalizadeh\SMS\Drivers\Contracts\Driver;
use Omalizadeh\SMS\Drivers\Contracts\TemplateSMSSender;
use Omalizadeh\SMS\Exceptions\SMSDriverNotFoundException;
use Omalizadeh\SMS\Exceptions\InvalidSMSConfigurationException;
use Omalizadeh\SMS\Requests\SendBulkSMSRequest;
use Omalizadeh\SMS\Requests\SendSMSRequest;
use Omalizadeh\SMS\Requests\SendTemplateSMSRequest;
use Omalizadeh\SMS\Responses\SendBulkSMSResponse;
use Omalizadeh\SMS\Responses\SendSMSResponse;
use ReflectionClass;

class SMS
{
    protected string $provider;

    protected array $driverConfig;

    protected Driver $driver;

    /**
     * Send a sms message to a phone number.
     *
     * @param SendSMSRequest $request
     * @return SendSMSResponse
     */
    public function send(SendSMSRequest $request): SendSMSResponse
    {
        return $this->getDriver()->send($request);
    }

    /**
     * Send a sms message with predefined template/pattern to a phone number.
     *
     * @param SendTemplateSMSRequest $request
     * @return SendSMSResponse
     * @throws SMSDriverNotFoundException
     */
    public function sendTemplate(SendTemplateSMSRequest $request): SendSMSResponse
    {
        $this->validateDriverImplementsTargetInterface(TemplateSMSSender::class);

        return $this->getDriver()->sendTemplate($request);
    }

    /**
     * Send a sms message to an array of phone numbers.
     *
     * @param SendBulkSMSRequest $request
     * @return SendBulkSMSResponse
     * @throws SMSDriverNotFoundException
     */
    public function sendBulk(SendBulkSMSRequest $request): SendBulkSMSResponse
    {
        $this->validateDriverImplementsTargetInterface(BulkSMSSender::class);

        return $this->getDriver()->sendBulk($request);
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        $this->setDriverConfig();
        $this->setDriver();

        return $this;
    }

    public function getProvider(): string
    {
        if (empty($this->provider)) {
            $this->setDefaultDriver();
        }

        return $this->provider;
    }

    protected function getDriver(): Driver
    {
        if (empty($this->driver)) {
            $this->setDefaultDriver();
        }

        return $this->driver;
    }

    private function setDriverConfig(): void
    {
        $driverConfig = config($this->getProviderConfigKey());

        if (empty($driverConfig) || !is_array($driverConfig)) {
            throw new InvalidSMSConfigurationException($this->getProvider() . ' configurations not found.');
        }

        $this->driverConfig = $driverConfig;
    }

    private function setDriver(): void
    {
        $class = config($this->getDriverNamespaceConfigKey());

        if (empty($class)) {
            throw new SMSDriverNotFoundException($this->getProvider() . ' driver class not defined.');
        }

        if (!class_exists($class)) {
            throw new SMSDriverNotFoundException(
                $this->getProvider() . ' driver class not found. try updating the package.',
            );
        }

        $driver = new $class($this->driverConfig);

        if (!is_subclass_of($driver, Driver::class)) {
            throw new SMSDriverNotFoundException(
                $this->getProvider() . ' driver class does not extend main driver class.',
            );
        }

        $this->driver = $driver;
    }

    private function getProviderConfigKey(): string
    {
        return 'sms.' . $this->getProvider();
    }

    private function getDriverNamespaceConfigKey(): string
    {
        return $this->getProviderConfigKey() . '.driver';
    }

    private function setDefaultDriver(): void
    {
        $defaultProvider = config('sms.default_provider');

        if (empty($defaultProvider) || !is_string($defaultProvider)) {
            throw new SMSDriverNotFoundException('sms provider not selected or default provider does not exist.');
        }

        $this->setProvider($defaultProvider);
    }

    private function validateDriverImplementsTargetInterface(string $interface): void
    {
        $reflect = new ReflectionClass($this->getDriver());

        if (!$reflect->implementsInterface($interface)) {
            throw new SMSDriverNotFoundException("Driver does not implement $interface.");
        }
    }
}
