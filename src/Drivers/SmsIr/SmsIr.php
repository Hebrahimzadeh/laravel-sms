<?php

namespace Omalizadeh\SMS\Drivers\SMSIr;

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

class SMSIr extends Driver implements BulkSMSSender, TemplateSMSSender
{
    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     */
    public function send(SendSMSRequest $request): SendSMSResponse
    {
        $data = [
            'Mobiles' => [$request->getPhoneNumber()],
            'MessageText' => $request->getMessage(),
            'lineNumber' => $request->getSender() ?: $this->getConfig('default_line_number'),
        ];
        $responseJson = $this->callApi($this->getSMSSendingURL(), $data);
        $responseData = $responseJson['data'];

        return new SendSMSResponse($responseData['messageIds'][0], $responseData['cost']);
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws InvalidSMSParameterException
     * @throws SendingSMSFailedException
     */
    public function sendBulk(SendBulkSMSRequest $request): SendBulkSMSResponse
    {
        if (count($request->getPhoneNumbers()) > 100) {
            throw new InvalidSMSParameterException(
                'SMS.ir does not support more than 100 phone numbers in bulk sms sending.',
            );
        }

        $data = [
            'Mobiles' => $request->getPhoneNumbers(),
            'MessageText' => $request->getMessage(),
            'lineNumber' => $request->getSender() ?: $this->getConfig('default_line_number'),
        ];

        $responseJson = $this->callApi($this->getBulkSMSSendingURL(), $data);
        $responseData = $responseJson['data'];

        return new SendBulkSMSResponse(
            array_map(fn(int $messageId) => new SendSMSResponse($messageId), $responseData['messageIds']),
            $responseData['packId'],
            $responseData['cost'],
        );
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     */
    public function sendTemplate(SendTemplateSMSRequest $request): SendSMSResponse
    {
        $template = $request->getTemplate();
        $template = is_int($template) ? $template : (int) $template;
        $parameters = [];

        foreach ($request->getParameters() as $key => $value) {
            $parameters[] = [
                'Name' => (string) $key,
                'Value' => (string) $value,
            ];
        }

        $data = [
            'Mobile' => $request->getPhoneNumber(),
            'TemplateId' => $template,
            'Parameters' => $parameters,
        ];

        $responseJson = $this->callApi($this->getTemplateSMSSendingURL(), $data);
        $responseData = $responseJson['data'];

        return new SendSMSResponse($responseData['messageId'], $responseData['cost']);
    }

    public function getSMSSendingURL(): string
    {
        return 'https://api.sms.ir/v1/send/bulk';
    }

    public function getBulkSMSSendingURL(): string
    {
        return $this->getSMSSendingURL();
    }

    public function getTemplateSMSSendingURL(): string
    {
        return 'https://api.sms.ir/v1/send/verify';
    }

    protected function callApi(string $url, array $data)
    {
        if (empty($apiKey = $this->getConfig('api_key'))) {
            throw new InvalidSMSConfigurationException('invalid api_key sms provider config');
        }

        $response = Http::asJson()
            ->acceptJson()
            ->withHeader('X-API-KEY', $apiKey)
            ->post($url, $data);

        $responseJson = $response->json();

        if (!isset($responseJson['status'])) {
            throw new SendingSMSFailedException(
                'Invalid response from sms.ir: ' . $response->body(),
                $response->status(),
            );
        }

        $status = $responseJson['status'];

        if ($status !== 1) {
            throw new SendingSMSFailedException($this->getStatusMessage($status), $status);
        }

        return $responseJson;
    }

    protected function getStatusMessage($statusCode): string
    {
        $messages = [
            0 => 'مشکلی در سامانه رخ داده است، لطفا با پشتیبانی در تماس باشید',
            1 => 'عملیات با موفقیت انجام شد',
            10 => 'کلیدوب سرویس نامعتبر است شد',
            11 => 'کلید وب سرویس غیرفعال است',
            12 => 'کلیدوب‌ سرویس محدود به IP‌های تعریف شده می‌باشد',
            13 => 'حساب کاربری غیر فعال است',
            14 => 'حساب کاربری در حالت تعلیق قرار دارد',
            20 => 'تعداد درخواست بیشتر از حد مجاز است',
            101 => 'شماره خط نامعتبر میباشد',
            102 => 'اعتبار کافی نمیباشد',
            103 => 'درخواست شما دارای متن(های) خالی است',
            104 => 'درخواست شما دارای موبایل(های) نادرست است',
            105 => 'تعداد موبایل ها بیشتر از حد مجاز (100عدد)میباشد',
            106 => 'تعداد متن ها بیشتر ازحد مجاز (100عدد) میباشد',
            107 => 'لیست موبایل ها خالی میباشد',
            108 => 'لیست متن ها خالی میباشد',
            109 => 'زمان ارسال نامعتبر میباشد',
            110 => 'تعداد شماره موبایل ها و تعداد متن ها برابر نیستند',
            111 => 'با این شناسه ارسالی ثبت نشده است',
            112 => 'رکوردی برای حذف یافت نشد',
            113 => 'قالب یافت نشد',
            114 => 'طول رشته مقدار پارامتر، بیش از حد مجاز (25 کاراکتر) می‌باشد',
            115 => 'شماره موبایل(ها) در لیست سیاه سامانه می‌باشند',
            116 => 'نام پارامتر نمی‌تواند خالی باشد',
            117 => 'متن ارسال شده مورد تایید نمی‌باشد',
            118 => 'تعداد پیام ها بیش از حد مجاز می باشد.',
            119 => 'به منظور استفاده از قالب‌ شخصی سازی شده پلن خود را ارتقا دهید',
            123 => 'خط ارسال‌کننده نیاز به فعال‌سازی دارد',
        ];

        return $messages[$statusCode] ?? 'خطای ناشناخته رخ داده است.';
    }
}
