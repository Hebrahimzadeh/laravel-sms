<?php

namespace Omalizadeh\SMS\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Omalizadeh\SMS\Drivers\SMSIr\SMSIr;
use Omalizadeh\SMS\Requests\SendTemplateSMSRequest;

class SMSIrTest extends TestCase
{
    protected SMSIr $smsIr;

    protected function setUp(): void
    {
        parent::setUp();

        config(['sms.sms_ir.api_key' => 'test_api_key']);

        $this->smsIr = new SMSIr(config('sms.sms_ir'));
    }

    public function test_template_sms_can_be_sent_successfully_by_sms_ir_driver(): void
    {
        Http::fake([
            'https://api.sms.ir/v1/send/verify' => Http::response([
                'status' => 1,
                'message' => 'موفق',
                'data' => [
                    'messageId' => 89545112,
                    'cost' => 1,
                ],
            ]),
        ]);

        $response = $this->smsIr->sendTemplate(
            new SendTemplateSMSRequest(
                '+989123456789',
                '123',
                [
                    'coupon_code' => 'HBD',
                ],
                '+981000',
            ),
        );

        $this->assertEquals(89545112, $response->getMessageId());
        $this->assertEquals(1, $response->getCost());

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.sms.ir/v1/send/verify'
                && $request->hasHeader('X-API-KEY', 'test_api_key')
                && $request['Mobile'] === '+989123456789'
                && $request['TemplateId'] === 123
                && $request['Parameters'] === [
                    [
                        'Name' => 'coupon_code',
                        'Value' => 'HBD',
                    ],
                ];
        });
    }
}
