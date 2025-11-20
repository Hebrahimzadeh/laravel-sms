<?php

namespace Omalizadeh\SMS\Drivers\Kavenegar;

use Illuminate\Support\Facades\Http;
use Omalizadeh\SMS\Drivers\Contracts\BulkSMSSender;
use Omalizadeh\SMS\Drivers\Contracts\Driver;
use Omalizadeh\SMS\Drivers\Contracts\TemplateSMSSender;
use Omalizadeh\SMS\Exceptions\InvalidSMSConfigurationException;
use Omalizadeh\SMS\Exceptions\InvalidSMSParameterException;
use Omalizadeh\SMS\Exceptions\SendingSMSFailedException;
use Omalizadeh\SMS\Requests\SendBulkSMSRequest;
use Omalizadeh\SMS\Requests\SendSMSRequest;
use Omalizadeh\SMS\Requests\SendTemplateSMSRequest;
use Omalizadeh\SMS\Responses\SendBulkSMSResponse;
use Omalizadeh\SMS\Responses\SendSMSResponse;

class Kavenegar extends Driver implements BulkSMSSender, TemplateSMSSender
{
    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     */
    public function send(SendSMSRequest $request): SendSMSResponse
    {
        $data = [
            'receptor' => $request->getPhoneNumber(),
            'message' => $request->getMessage(),
            'sender' => $request->getSender() ?: $this->getConfig('default_sender'),
        ];
        $responseJson = $this->callApi($this->getSMSSendingURL(), $data);

        return Entry::fromArray($responseJson['entries'][0])->toSendSMSResponse();
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws InvalidSMSParameterException
     * @throws SendingSMSFailedException
     */
    public function sendBulk(SendBulkSMSRequest $request): SendBulkSMSResponse
    {
        if (count($request->getPhoneNumbers()) > 200) {
            throw new InvalidSMSParameterException(
                'Kavenegar bulk sms sending does not support more than 200 phone numbers.',
            );
        }

        $data = [
            'receptor' => implode(',', $request->getPhoneNumbers()),
            'message' => $request->getMessage(),
            'sender' => $request->getSender() ?: $this->getConfig('default_sender'),
        ];
        $responseJson = $this->callApi($this->getBulkSMSSendingURL(), $data);
        $totalCost = 0;

        return new SendBulkSMSResponse(
            records: array_map(
                function (array $entryArray) use (&$totalCost) {
                    $entry = Entry::fromArray($entryArray);
                    $totalCost += $entry->getCost();

                    return $entry->toSendSMSResponse();
                },
                $responseJson['entries'],
            ),
            totalCost: $totalCost,
        );
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     */
    public function sendTemplate(SendTemplateSMSRequest $request): SendSMSResponse
    {
        $template = $request->getTemplate();
        $template = is_string($template) ? $template : (string) $template;
        $data = [
            'receptor' => $request->getPhoneNumber(),
            'template' => $template,
        ];
        $requestParameters = $request->getParameters();

        if (array_is_list($requestParameters)) {
            $parameters['token'] = $requestParameters[0];
            $parameters['token2'] = $requestParameters[1] ?? null;
            $parameters['token3'] = $requestParameters[2] ?? null;
            $parameters['token10'] = $requestParameters[3] ?? null;
            $parameters['token20'] = $requestParameters[4] ?? null;
        } else {
            $parameters = $requestParameters;
        }

        $data = array_merge($data, $parameters);
        $responseJson = $this->callApi($this->getTemplateSMSSendingURL(), $data);

        return Entry::fromArray($responseJson['entries'][0])->toSendSMSResponse();
    }

    /**
     * @throws InvalidSMSConfigurationException
     */
    public function getSMSSendingURL(): string
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidSMSConfigurationException('invalid api_key sms provider config');
        }

        return 'https://api.kavenegar.com/v1/' . $apiKey . '/sms/send.json';
    }

    /**
     * @throws InvalidSMSConfigurationException
     */
    public function getBulkSMSSendingURL(): string
    {
        return $this->getSMSSendingURL();
    }

    public function getTemplateSMSSendingURL(): string
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidSMSConfigurationException('invalid api_key sms provider config');
        }

        return 'https://api.kavenegar.com/v1/' . $apiKey . '/verify/lookup.json';
    }

    protected function callApi(string $url, array $data)
    {
        $response = Http::asForm()->acceptJson()->post($url, $data);
        $responseJson = $response->json();

        if (!isset($responseJson['return']['status'])) {
            throw new SendingSMSFailedException(
                'Invalid response from kavenegar: ' . $response->body(),
                $response->status(),
            );
        }

        $status = $responseJson['return']['status'];

        if ($status !== 200) {
            throw new SendingSMSFailedException($this->getStatusMessage($status), $status);
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

        return $messages[$statusCode] ?? 'خطای ناشناخته رخ داده است.';
    }
}
