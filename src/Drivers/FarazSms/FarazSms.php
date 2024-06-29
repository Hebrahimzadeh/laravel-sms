<?php

namespace Omalizadeh\Sms\Drivers\FarazSms;

use Illuminate\Support\Facades\Http;
use Omalizadeh\Sms\BulkSentSmsInfo;
use Omalizadeh\Sms\Drivers\Contracts\BulkSmsInterface;
use Omalizadeh\Sms\Drivers\Contracts\Driver;
use Omalizadeh\Sms\Drivers\Contracts\TemplateSmsInterface;
use Omalizadeh\Sms\Exceptions\InvalidConfigurationException;
use Omalizadeh\Sms\Exceptions\InvalidParameterException;
use Omalizadeh\Sms\Exceptions\SendingSmsFailedException;
use Omalizadeh\Sms\SentSmsInfo;

class FarazSms extends Driver implements BulkSmsInterface, TemplateSmsInterface
{
    public function send(string $phoneNumber, string $message, array $options = []): SentSmsInfo
    {
        $data = [
            'recipient' => [$phoneNumber],
            'message' => trim($message),
        ];

        $data = $this->mergeSmsOptions($data, $options);

        $responseJson = $this->callApi($this->getSingleSmsUrl(), $data);

        if (empty($responseJson['data']['message_id'])) {
            throw new SendingSmsFailedException(
                'sent sms details not found in response',
                $responseJson['status'],
            );
        }

        return new SentSmsInfo($responseJson['data']['message_id'], 0);
    }

    public function sendTemplate(string $phoneNumber, $template, array $options = []): SentSmsInfo
    {
        $template = is_string($template) ? $template : (string)$template;

        $data = [
            'recipient' => $phoneNumber,
            'code' => $template,
        ];

        $data = $this->mergeSmsOptions($data, $options);
        $data = $this->mergeTemplateOptions($data, $options);

        $responseJson = $this->callApi($this->getTemplateSmsUrl(), $data);

        if (empty($responseJson['data']['message_id'])) {
            throw new SendingSmsFailedException(
                'sent sms details not found in response',
                $responseJson['status'],
            );
        }

        return new SentSmsInfo($responseJson['data']['message_id'], 0);
    }

    public function sendBulk(array $phoneNumbers, string $message, array $options = []): BulkSentSmsInfo
    {
        $data = [
            'recipient' => $phoneNumbers,
            'message' => trim($message),
        ];

        $data = $this->mergeSmsOptions($data, $options);

        $responseJson = $this->callApi($this->getBulkSmsUrl(), $data);

        if (empty($responseJson['data']['message_id'])) {
            throw new SendingSmsFailedException(
                'sent sms details not found in response',
                $responseJson['status'],
            );
        }

        return new BulkSentSmsInfo([$responseJson['data']['message_id']], 0);
    }

    public function getSingleSmsUrl(): string
    {
        return $this->getBulkSmsUrl();
    }

    public function getTemplateSmsUrl(): string
    {
        return 'https://api2.ippanel.com/api/v1/sms/pattern/normal/send';
    }

    public function getBulkSmsUrl(): string
    {
        return 'https://api2.ippanel.com/api/v1/sms/send/webservice/single';
    }

    protected function mergeSmsOptions(array $data, array $options): array
    {
        if (empty($options)) {
            return $data;
        }

        if (!isset($options['sender'])) {
            throw new InvalidParameterException('sender parameter is required.');
        }

        return array_merge($data, [
            'sender' => $options['sender'],
            'time' => $options['time'] ?? now()->addSecond(),
        ]);
    }

    protected function mergeTemplateOptions(array $data, array $options): array
    {
        if (!isset($options['token'])) {
            throw new InvalidParameterException('token option is required when using sms with template');
        }

        return array_merge($data, [
            'variable' => $options['token']
        ]);
    }

    protected function callApi(string $url, array $data)
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidConfigurationException('invalid api_key sms provider config');
        }

        $response = Http::asJson()->acceptJson()->withHeaders([
            'apiKey' => $apiKey,
        ])->post($url, $data);

        $responseJson = $response->json();

        if (!isset($responseJson['status'])) {
            throw new SendingSmsFailedException($this->getStatusMessage($response->status()), $response->status());
        }

        $status = $responseJson['code'];

        if ($status !== $this->getSuccessfulStatusCode()) {
            throw new SendingSmsFailedException($this->getStatusMessage($status), $status);
        }

        return $responseJson;
    }

    protected function getStatusMessage($statusCode): string
    {
        $messages = [
            0 => 'عملیات با موفقیت انجام شده است.',
            1 => 'متن پیام خالی می باشد.',
            2 => 'کاربر محدود گردیده است.',
            3 => 'خط به شما تعلق ندارد.',
            4 => 'گیرندگان خالی است.',
            5 => 'اعتبار کافی نیست.',
            6 => 'خط مورد نظر برای ارسال انبوه مناسب نمی‌باشد.',
            7 => 'خط مورد نظر در این ساعت امکان ارسال ندارد.',
            8 => 'حداکثر تعداد گیرنده رعایت نشده است.',
            9 => 'اپراتور خط ارسالی قطع می‌باشد.',
            21 => 'پسوند فایل صوتی نامعتبر است.',
            22 => "سایز فایل صوتی نامعتبر است.",
            23 => "تعداد تلاش در پیام صوتی نامعتبر است.",
            100 => "شماره مخاطب دفترچه تلفن نامعتبر می‌باشد.",
            101 => "شماره مخاطب در دفترچه تلفن وجود دارد.",
            102 => "شماره مخاطب با موفقیت در دفترچه تلفن ذخیره گردید.",
            111 => "حداکثر تعداد گیرنده برای ارسال پیام صوتی رعایت نشده است.",
            131 => "تعداد تلاش در پیام صوتی باید یکبار باشد.",
            132 => "آدرس فایل صوتی وارد نگردیده است.",
            301 => "از حرف ویژه در نام کاربری استفاده گردیده است.",
            302 => "قیمت گذاری انجام نشده است.",
            303 => "نام کاربری وارد نگردیده است.",
            304 => "نام کاربری قبلا انتخاب گردیده است.",
            305 => "نام کاربری وارد نگردیده است.",
            306 => "کد ملی وارد نشده است.",
            307 => "کد ملی به خطا وارد شده است.",
            308 => "شماره شناسنامه نا معتبر است.",
            309 => "شماره شناسنامه وارد نگردیده است.",
            310 => "ایمیل کاربر وارد نگردیده است.",
            311 => "شماره تلفن وارد نگردیده است.",
            312 => "تلفن به درستی وارد نگردیده است.",
            313 => "آدرس شما وارد نگردیده است.",
            314 => "شماره موبایل را وارد نکرده اید.",
            315 => "شماره موبایل به نادرستی وارد گردیده است.",
            316 => "سطح دسترسی به نادرستی وارد گردیده است.",
            317 => "کلمه عبور وارد نشده است.",
            455 => "ارسال در آینده برای کد بالک ارسالی لغو شد.",
            456 => "کد بالک ارسالی نامعتبر است.",
            458 => "کد تیکت نامعتبر است.",
            964 => "شما دسترسی نمایندگی ندارید.",
            962 => "نام کاربری یا کلمه عبور نادرست می باشد.",
            963 => "دسترسی نامعتبر می باشد.",
            971 => "پترن ارسالی نامعتبر است.",
            970 => "پارامتر های ارسالی برای پترن نامعتبر است.",
            972 => "دریافت کننده برای ارسال پترن نامعتبر می باشد.",
            992 => "ارسال پیام از ساعت 8 تا 23 می باشد.",
            993 => "دفترچه تلفن باید یک آرایه باشد",
            994 => "لطفا تصویری از کارت بانکی خود را از منو مدارک ارسال کنید",
            995 => "جهت ارسال با خطوط اشتراکی سامانه، لطفا شماره کارت بانکی خود را به دلیل تکمیل فرایند احراز هویت از بخش ارسال مدارک ثبت نمایید.",
            996 => "پترن فعال نیست.",
            997 => "شما اجازه ارسال از این پترن را ندارید.",
            998 => "کارت ملی یا کارت بانکی شما تایید نشده است.",
            1001 => "فرمت نام کاربری درست نمی باشد،حداقل ۵ کاراکتر(فقط حروف و اعداد)",
            1002 => "گذرواژه خیلی ساده می باشد.\n(حداقل ۸ کاراکتر بوده و نام کاربری، ایمیل و شماره موبایل در آن وجود نداشته باشد.)",
            1003 => "مشکل در ثبت، با پشتیبانی تماس بگیرید.",
            1004 => "مشکل در ثبت، با پشتیبانی تماس بگیرید.",
            1005 => "مشکل در ثبت، با پشتیبانی تماس بگیرید.",
            1006 => "تاریخ ارسال پیام برای گذشته می باشد، لطفا تاریخ ارسال پیام را به درستی وارد نمایید."

        ];

        return $messages[$statusCode] ?? 'خطای ناشناخته رخ داده است.';
    }

    protected function getSuccessfulStatusCode(): int
    {
        return 200;
    }
}
