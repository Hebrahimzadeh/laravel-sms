<?php

namespace Omalizadeh\Sms\Drivers\Kavenegar;

use Illuminate\Support\Facades\Http;
use Omalizadeh\Sms\Drivers\Contracts\BulkSmsInterface;
use Omalizadeh\Sms\Drivers\Contracts\Driver;
use Omalizadeh\Sms\Drivers\Contracts\TemplateSmsInterface;
use Omalizadeh\Sms\Exceptions\InvalidConfigurationException;
use Omalizadeh\Sms\Exceptions\InvalidParameterException;

class Kavenegar extends Driver implements BulkSmsInterface, TemplateSmsInterface
{
    public function send(string $phoneNumber, string $message, array $options = [])
    {
        $data = [
            'receptor' => $phoneNumber,
            'message' => trim($message),
        ];

        $data = $this->mergeSmsOptions($data, $options);

        return Http::post($this->getSingleSmsUrl(), $data)->throw()->json();
    }

    public function sendBulk(array $phoneNumbers, string $message, array $options = [])
    {
        if (count($phoneNumbers) > 200) {
            throw new InvalidParameterException('phone numbers count exceeds max value of 200');
        }

        $data = [
            'receptor' => implode(',', $phoneNumbers),
            'message' => trim($message),
        ];

        $data = $this->mergeSmsOptions($data, $options);

        return Http::post($this->getSingleSmsUrl(), $data)->throw()->json();
    }

    public function sendTemplate(string $phoneNumber, $template, array $options = [])
    {
        if (!is_string($template)) {
            throw new InvalidParameterException('template must be a string value');
        }

        $data = [
            'receptor' => $phoneNumber,
            'template' => $template,
        ];

        $data = $this->mergeTemplateOptions($data, $options);

        return Http::post($this->getSingleSmsUrl(), $data)->throw()->json();
    }

    public function getSingleSmsUrl(): string
    {
        return $this->getBulkSmsUrl();
    }

    public function getBulkSmsUrl(): string
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidConfigurationException('invalid api_key sms provider config');
        }

        return 'https://api.kavenegar.com/v1/'.$apiKey.'/sms/send.json';
    }

    public function getTemplateSmsUrl(): string
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidConfigurationException('invalid api_key sms provider config');
        }

        return 'https://api.kavenegar.com/v1/'.$apiKey.'/verify/lookup.json';
    }

    protected function mergeSmsOptions(array $data, array $options): array
    {
        if (empty($options)) {
            return $data;
        }

        return array_merge($data, [
            'sender' => $options['sender'] ?? null,
            'date' => $options['date'] ?? null,
            'type' => $options['type'] ?? null,
            'localid' => $options['local_id'] ?? null,
            'hide' => $options['hide'] ?? null,
        ]);
    }

    protected function mergeTemplateOptions(array $data, array $options): array
    {
        if (!isset($options['token'])) {
            throw new InvalidParameterException('token options is required when using sms with template');
        }

        return array_merge($data, [
            'token' => $options['token'],
            'token2' => $options['token2'] ?? null,
            'token3' => $options['token3'] ?? null,
            'type' => $options['type'] ?? null,
        ]);
    }
}
