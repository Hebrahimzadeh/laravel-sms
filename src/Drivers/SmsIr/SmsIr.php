<?php

namespace Omalizadeh\Sms\Drivers\SmsIr;

use Illuminate\Support\Facades\Http;
use Omalizadeh\Sms\BulkSentSmsInfo;
use Omalizadeh\Sms\Drivers\Contracts\BulkSmsInterface;
use Omalizadeh\Sms\Drivers\Contracts\Driver;
use Omalizadeh\Sms\Drivers\Contracts\TemplateSmsInterface;
use Omalizadeh\Sms\Exceptions\InvalidConfigurationException;
use Omalizadeh\Sms\Exceptions\InvalidParameterException;
use Omalizadeh\Sms\Exceptions\SendingSmsFailedException;
use Omalizadeh\Sms\SentSmsInfo;

class SmsIr extends Driver implements BulkSmsInterface, TemplateSmsInterface
{
    public function send(string $phoneNumber, string $message, array $options = []): SentSmsInfo
    {
        $data = [
            'Mobiles' => [$phoneNumber],
            'MessageText' => $message,
        ];

        $data = $this->mergeSmsOptions($data, $options);

        $responseJson = $this->callApi($this->getSingleSmsUrl(), $data);

        if (empty($responseJson['messageIds'])) {
            throw new SendingSmsFailedException(
                'sent sms details not found in response',
                $responseJson['status'],
            );
        }

        return new SentSmsInfo($responseJson['messageIds'][0], $responseJson['cost']);
    }

    public function sendTemplate(string $phoneNumber, $template, array $options = []): SentSmsInfo
    {
        $template = is_int($template) ? $template : (int) $template;

        $data = [
            'Mobile' => $phoneNumber,
            'TemplateId' => $template,
        ];

        $data = $this->mergeTemplateOptions($data, $options);

        $responseJson = $this->callApi($this->getTemplateSmsUrl(), $data);

        if (!isset($responseJson['messageId'])) {
            throw new SendingSmsFailedException(
                'sent sms details not found in response',
                $responseJson['status'],
            );
        }

        return new SentSmsInfo($responseJson['messageId'], $responseJson['cost']);
    }

    public function sendBulk(array $phoneNumbers, string $message, array $options = []): BulkSentSmsInfo
    {
        if (count($phoneNumbers) > 100) {
            throw new InvalidParameterException('phone numbers count exceeds max value of 100');
        }

        $data = [
            'Mobiles' => $phoneNumbers,
            'MessageText' => $message,
        ];

        $data = $this->mergeSmsOptions($data, $options);

        $responseJson = $this->callApi($this->getBulkSmsUrl(), $data);

        if (empty($responseJson['messageIds'])) {
            throw new SendingSmsFailedException(
                'sent sms details not found in response',
                $responseJson['status'],
            );
        }

        return new BulkSentSmsInfo($responseJson['messageIds'], $responseJson['cost']);
    }

    public function getSingleSmsUrl(): string
    {
        return $this->getBulkSmsUrl();
    }

    public function getTemplateSmsUrl(): string
    {
        return 'https://api.sms.ir/v1/send/verify';
    }

    public function getBulkSmsUrl(): string
    {
        return 'https://api.sms.ir/v1/send/bulk';
    }

    protected function mergeSmsOptions(array $data, array $options): array
    {
        if (empty($options)) {
            return $data;
        }

        return array_merge($data, [
            'lineNumber' => $options['lineNumber'] ?? null,
            'SendDateTime' => $options['SendDateTime'] ?? null,
        ]);
    }

    protected function mergeTemplateOptions(array $data, array $options): array
    {
        if (empty($options) || !isset($options[0]['Name'], $options[0]['Value'])) {
            throw new InvalidParameterException('Name and Value are required in options');
        }

        return array_merge($data, [
            'Parameters' => $options,
        ]);
    }

    protected function callApi(string $url, array $data)
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidConfigurationException('invalid api_key sms provider config');
        }

        $response = Http::asJson()->acceptJson()->withHeaders([
            'X-API-KEY' => $apiKey,
        ])->post($url, $data);

        $responseJson = $response->json();

        if (!isset($responseJson['status'])) {
            throw new SendingSmsFailedException($this->getStatusMessage($response->status()), $response->status());
        }

        $status = $responseJson['status'];

        if ($status !== $this->getSuccessfulStatusCode()) {
            throw new SendingSmsFailedException($this->getStatusMessage($status), $status);
        }

        return $responseJson;
    }

    protected function getStatusMessage($statusCode): string
    {
        $messages = [
            0 => 'مشکلی در سامانه رخ داده است، لطفا با پشتیبانی در تماس باشید',
            1 => 'عملیات با موفقیت انجام شد',
            10 => 'کلید وب سرویس نامعتبر است',
            11 => 'کلید وب سرویس غیرفعال است',
            12 => 'کلید وب‌ سرویس محدود به IP‌های تعریف شده می‌باشد',
            13 => 'حساب کاربری غیر فعال است',
            14 => 'حساب کاربری در حالت تعلیق قرار دارد',
            20 => 'تعداد درخواست بیشتر از حد مجاز است',
            101 => 'شماره خط نامعتبر میباشد',
            102 => 'اعتبار کافی نمیباشد',
            103 => 'درخواست شما دارای متن (های) خالی است',
            104 => 'درخواست شما دارای موبایل (های) نادرست است',
            105 => 'تعداد موبایل ها بیشتر از حد مجاز (100 عدد) میباشد',
            106 => 'تعداد متن ها بیشتر از حد مجاز (100 عدد) میباشد',
            107 => 'لیست موبایل ها خالی میباشد',
            108 => 'لیست متن ها خالی میباشد',
            109 => 'زمان ارسال نامعتبر میباشد',
            110 => 'تعداد شماره موبایل ها و تعداد متن ها برابر نیستند',
            111 => 'با این شناسه ارسالی ثبت نشده است',
            112 => 'رکوردی برای حذف یافت نشد',
            113 => 'قالب یافت نشد',
            400 => 'وقوع خطای منطقی',
            401 => 'وجود خطا در فرآیند احراز هویت',
            429 => 'تعداد درخواست غیر مجاز',
            500 => 'خطای غیر منتظره',
        ];

        return $messages[$statusCode] ?? 'خطای ناشناخته رخ داده است.';
    }

    protected function getSuccessfulStatusCode(): int
    {
        return 1;
    }
}
