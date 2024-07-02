<?php

namespace Omalizadeh\Sms\Tests;


use Illuminate\Support\Facades\Http;
use Omalizadeh\Sms\Drivers\FarazSms\FarazSms;
use Omalizadeh\Sms\Exceptions\SendingSmsFailedException;

class FarazSmsTest extends TestCase
{
    protected FarazSms $farazSms;

    protected function setUp(): void
    {
        parent::setUp();

        config(['sms.faraz_sms.api_key' => 'test_api_key']);

        $this->farazSms = new FarazSms(config('sms.faraz_sms'));
    }

    public function test_sms_can_be_sent_successfully_by_faraz_driver()
    {
        Http::fake([
            'https://api2.ippanel.com/api/v1/sms/send/webservice/single' => Http::response([
                'status' => 'OK',
                'code' => 200,
                'data' => [
                    'message_id' => '123456789'
                ],
                'error_message' => null
            ], 200)
        ]);

        $result = $this->farazSms->send('09123456789', 'Test message', ['sender' => '1000']);

        $this->assertEquals('123456789', $result->getMessageId());

        Http::assertSent(function ($request) {
            return $request->url() == 'https://api2.ippanel.com/api/v1/sms/send/webservice/single' &&
                $request['recipient'] == ['09123456789'] &&
                $request['message'] == 'Test message' &&
                $request['sender'] == '1000' &&
                $request->header('apiKey')[0] == 'test_api_key';
        });
    }

    public function test_sms_can_get_failed_successfully()
    {
        Http::fake([
            'https://api2.ippanel.com/api/v1/sms/send/webservice/single' => Http::response([
                'status' => 'error',
                'code' => 5,
                'message' => 'اعتبار کافی نیست.'
            ], 200)
        ]);

        $this->expectException(SendingSmsFailedException::class);
        $this->expectExceptionMessage('اعتبار کافی نیست.');

        $this->farazSms->send('09123456789', 'Test message', ['sender' => '1000']);
    }

    public function test_invalid_response_structure_exception()
    {
        Http::fake([
            'https://api2.ippanel.com/api/v1/sms/send/webservice/single' => Http::response([
                'unexpected' => 'response'
            ], 200)
        ]);

        $this->expectException(SendingSmsFailedException::class);

        $this->farazSms->send('09123456789', 'Test message', ['sender' => '1000']);
    }

    public function test_missing_sender_option_leads_to_exception()
    {
        $this->expectException(\Omalizadeh\Sms\Exceptions\InvalidParameterException::class);
        $this->expectExceptionMessage('sender parameter is required.');

        $this->farazSms->send('09123456789', 'Test message');
    }
}
