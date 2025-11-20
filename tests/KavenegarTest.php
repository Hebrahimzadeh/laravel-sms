<?php

namespace Omalizadeh\SMS\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Omalizadeh\SMS\Drivers\Kavenegar\Kavenegar;
use Omalizadeh\SMS\Requests\SendTemplateSMSRequest;

class KavenegarTest extends TestCase
{
    protected Kavenegar $kavenegar;

    protected function setUp(): void
    {
        parent::setUp();

        config(['sms.kavenegar.api_key' => 'test_api_key']);

        $this->kavenegar = new Kavenegar(config('sms.kavenegar'));
    }

    public function test_template_sms_can_be_sent_successfully_by_kavenegar_driver(): void
    {
        Http::fake([
            'https://api.kavenegar.com/v1/test_api_key/verify/lookup.json' => Http::response([
                'return' => [
                    'status' => 200,
                    'message' => 'تایید شد',
                ],
                'entries' => [
                    [
                        'messageid' => 8792343,
                        'message' => 'عضویت شما تایید شد: 84456',
                        'status' => 5,
                        'statustext' => 'ارسال به مخابرات',
                        'sender' => '10004346',
                        'receptor' => '09*********',
                        'date' => 1356619709,
                        'cost' => 120,
                    ],
                ],
            ]),
        ]);

        $response = $this->kavenegar->sendTemplate(
            new SendTemplateSMSRequest(
                '+989123456789',
                'auth',
                [
                    'YourName',
                    '1234',
                ],
                '+981000',
            ),
        );

        $this->assertEquals(8792343, $response->getMessageId());
        $this->assertEquals(120, $response->getCost());

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.kavenegar.com/v1/test_api_key/verify/lookup.json'
                && $request['receptor'] === '+989123456789'
                && $request['template'] === 'auth'
                && $request['token'] === 'YourName'
                && $request['token2'] === '1234';
        });
    }
}
