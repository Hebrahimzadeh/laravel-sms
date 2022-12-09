<?php

namespace Omalizadeh\Sms\Drivers\Kavenegar;

use Illuminate\Support\Facades\Http;
use Omalizadeh\Sms\BulkSentSmsInfo;
use Omalizadeh\Sms\Drivers\Contracts\BulkSmsInterface;
use Omalizadeh\Sms\Drivers\Contracts\Driver;
use Omalizadeh\Sms\Drivers\Contracts\TemplateSmsInterface;
use Omalizadeh\Sms\Exceptions\InvalidConfigurationException;
use Omalizadeh\Sms\Exceptions\InvalidParameterException;
use Omalizadeh\Sms\Exceptions\SendingSmsFailedException;
use Omalizadeh\Sms\SentSmsInfo;

class Kavenegar extends Driver implements BulkSmsInterface, TemplateSmsInterface
{
    public function send(string $phoneNumber, string $message, array $options = []): SentSmsInfo
    {
        $data = [
            'receptor' => $phoneNumber,
            'message' => trim($message),
        ];

        $data = $this->mergeSmsOptions($data, $options);

        $responseJson = $this->callApi($this->getSingleSmsUrl(), $data);

        if (isset($responseJson['entries']) && !empty($responseJson['entries'])) {
            $smsDetail = array_pop($responseJson['entries']);

            return new SentSmsInfo($smsDetail['messageid'], $smsDetail['cost']);
        }

        throw new SendingSmsFailedException(
            'sent sms details not found in response',
            $responseJson['return']['status'],
        );
    }

    public function sendTemplate(string $phoneNumber, $template, array $options = []): SentSmsInfo
    {
        $template = is_string($template) ? $template : (string) $template;

        $data = [
            'receptor' => $phoneNumber,
            'template' => $template,
        ];

        $data = $this->mergeTemplateOptions($data, $options);

        $responseJson = $this->callApi($this->getTemplateSmsUrl(), $data);

        if (isset($responseJson['entries']) && !empty($responseJson['entries'])) {
            $smsDetail = array_pop($responseJson['entries']);

            return new SentSmsInfo($smsDetail['messageid'], $smsDetail['cost']);
        }

        throw new SendingSmsFailedException(
            'sent sms details not found in response',
            $responseJson['return']['status'],
        );
    }

    public function sendBulk(array $phoneNumbers, string $message, array $options = []): BulkSentSmsInfo
    {
        if (count($phoneNumbers) > 200) {
            throw new InvalidParameterException('phone numbers count exceeds max value of 200');
        }

        $data = [
            'receptor' => implode(',', $phoneNumbers),
            'message' => trim($message),
        ];

        $data = $this->mergeSmsOptions($data, $options);

        $responseJson = $this->callApi($this->getBulkSmsUrl(), $data);

        if (empty($responseJson['entries'])) {
            throw new SendingSmsFailedException(
                'sent sms details not found in response',
                $responseJson['return']['status'],
            );
        }

        $entries = collect($responseJson['entries']);

        return new BulkSentSmsInfo($entries->pluck('messageid')->toArray(), $entries->sum('cost'));
    }

    public function getSingleSmsUrl(): string
    {
        return $this->getBulkSmsUrl();
    }

    public function getTemplateSmsUrl(): string
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidConfigurationException('invalid api_key sms provider config');
        }

        return 'https://api.kavenegar.com/v1/'.$apiKey.'/verify/lookup.json';
    }

    public function getBulkSmsUrl(): string
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidConfigurationException('invalid api_key sms provider config');
        }

        return 'https://api.kavenegar.com/v1/'.$apiKey.'/sms/send.json';
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
            throw new InvalidParameterException('token option is required when using sms with template');
        }

        return array_merge($data, [
            'token' => $options['token'],
            'token2' => $options['token2'] ?? null,
            'token3' => $options['token3'] ?? null,
            'type' => $options['type'] ?? null,
        ]);
    }

    protected function callApi(string $url, array $data)
    {
        $response = Http::asForm()->acceptJson()->post($url, $data);

        $responseJson = $response->json();

        if (!isset($responseJson['return']['status'])) {
            throw new SendingSmsFailedException($this->getStatusMessage($response->status()), $response->status());
        }

        $status = $responseJson['return']['status'];

        if ($status !== $this->getSuccessfulStatusCode()) {
            throw new SendingSmsFailedException($this->getStatusMessage($status), $status);
        }

        return $responseJson;
    }

    protected function getStatusMessage($statusCode): string
    {
        $messages = [
            200 => 'درخواست تایید شد',
            400 => 'پارامترها ناقص هستند',
            401 => 'حساب کاربری غیرفعال شده است',
            402 => 'عملیات ناموفق بود',
            403 => 'کد شناسائی API-Key معتبر نمی‌باشد',
            404 => 'متد نامشخص است',
            405 => 'متد Get/Post اشتباه است',
            406 => 'پارامترهای اجباری خالی ارسال شده اند',
            407 => 'دسترسی به اطلاعات مورد نظر برای شما امکان پذیر نیست',
            409 => 'سرور قادر به پاسخگوئی نیست بعدا تلاش کنید',
            411 => 'دریافت کننده نامعتبر است',
            412 => 'ارسال کننده نامعتبر است',
            413 => 'پیام خالی است و یا طول پیام بیش از حد مجاز می‌باشد. حداکثر طول کل متن پیامک 900 کاراکتر می باشد',
            414 => 'حجم درخواست بیشتر از حد مجاز است،ارسال پیامک: هر فراخوانی حداکثر 200 رکورد و کنترل وضعیت: هر فراخوانی 500 رکورد',
            415 => 'اندیس شروع بزرگ تر از کل تعداد شماره های مورد نظر است',
            416 => 'IP سرویس مبدا با تنظیمات مطابقت ندارد',
            417 => 'تاریخ ارسال اشتباه است و فرمت آن صحیح نمی باشد.',
            418 => 'اعتبار شما کافی نمی‌باشد',
            419 => 'طول آرایه متن و گیرنده و فرستنده هم اندازه نیست',
            420 => 'استفاده از لینک در متن پیام برای شما محدود شده است',
            422 => 'داده ها به دلیل وجود کاراکتر نامناسب قابل پردازش نیست',
            424 => 'الگوی مورد نظر پیدا نشد',
            426 => 'استفاده از این متد نیازمند سرویس پیشرفته می‌باشد',
            427 => 'استفاده از این خط نیازمند ایجاد سطح دسترسی می‌باشد',
            428 => 'ارسال کد از طریق تماس تلفنی امکان پذیر نیست',
            429 => 'IP محدود شده است',
            431 => 'ساختار کد صحیح نمی‌باشد',
            432 => 'پارامتر کد در متن پیام پیدا نشد',
            451 => 'فراخوانی بیش از حد در بازه زمانی مشخص IP محدود شده',
            501 => 'فقط امکان ارسال پیام تست به شماره صاحب حساب کاربری وجود دارد',
        ];

        $unknownError = 'خطای ناشناخته رخ داده است.';

        return array_key_exists($statusCode, $messages) ? $messages[$statusCode] : $unknownError;
    }

    protected function getSuccessfulStatusCode(): int
    {
        return 200;
    }
}
