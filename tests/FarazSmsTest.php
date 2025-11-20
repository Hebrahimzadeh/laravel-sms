<?php

namespace Omalizadeh\SMS\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Omalizadeh\SMS\Drivers\FarazSMS\FarazSMS;
use Omalizadeh\SMS\Exceptions\InvalidSMSConfigurationException;
use Omalizadeh\SMS\Exceptions\SendingSMSFailedException;
use Omalizadeh\SMS\Requests\SendSMSRequest;

class FarazSMSTest extends TestCase
{
    protected FarazSMS $farazSMS;

    protected function setUp(): void
    {
        parent::setUp();

        config(['sms.faraz_sms.token' => 'test_token']);

        $this->farazSMS = new FarazSMS(config('sms.faraz_sms'));
    }

    public function test_sms_can_be_sent_successfully_by_faraz_driver(): void
    {
        Http::fake([
            'https://edge.ippanel.com/v1/api/send' => Http::response([
                'data' => [
                    'message_outbox_ids' => [
                        1123544244,
                    ],
                ],
                'meta' => [
                    'status' => true,
                    'message' => 'انجام شد',
                    'message_parameters' => [],
                    'message_code' => '200-1',
                ],
            ]),
        ]);

        $response = $this->farazSMS->send(
            new SendSMSRequest(
                '+989123456789',
                'Test message',
                '1000',
            ),
        );

        $this->assertEquals(1123544244, $response->getMessageId());
        $this->assertNull($response->getCost());

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://edge.ippanel.com/v1/api/send'
                && $request->hasHeader('Authorization', 'test_token')
                && $request['sending_type'] === 'webservice'
                && $request['from_number'] === '1000'
                && $request['params']['recipients'] === ['+989123456789'];
        });
    }

    public function test_sms_can_get_failed_successfully(): void
    {
        $this->expectException(SendingSMSFailedException::class);
        $this->expectExceptionMessage('اطلاعات وارد شده صحیح نمی باشد');

        Http::fake([
            'https://edge.ippanel.com/v1/api/send' => Http::response([
                'data' => null,
                'meta' => [
                    'status' => false,
                    'message' => 'اطلاعات وارد شده صحیح نمی باشد',
                    'message_parameters' => [],
                    'message_code' => '400-1',
                    'errors' => [],
                ],
            ]),
        ]);

        $this->farazSMS->send(
            new SendSMSRequest(
                '+989123456789',
                'Test message',
                '1000',
            ),
        );

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://edge.ippanel.com/v1/api/send'
                && $request->header('Authorization')[0] === 'test_token'
                && $request['sending_type'] === 'webservice'
                && $request['from_number'] === '1000'
                && $request['params']['recipients'] === ['+989123456789'];
        });
    }

    public function test_invalid_response_structure_exception(): void
    {
        $this->expectException(SendingSMSFailedException::class);
        $this->expectExceptionMessage('Invalid response from FarazSMS: {"unexpected":"error"}');

        Http::fake([
            'https://edge.ippanel.com/v1/api/send' => Http::response([
                'unexpected' => 'error',
            ]),
        ]);

        $this->farazSMS->send(
            new SendSMSRequest(
                '+989123456789',
                'Test message',
                '1000',
            ),
        );
    }

    public function test_missing_token_leads_to_exception(): void
    {
        $this->expectException(InvalidSMSConfigurationException::class);
        $this->expectExceptionMessage('Invalid faraz_sms token.');

        Http::fake();

        config([
            'sms.faraz_sms.token' => '',
        ]);

        $this->farazSMS = new FarazSMS(config('sms.faraz_sms'));

        $this->farazSMS->send(
            new SendSMSRequest(
                '+989123456789',
                'Test message',
                '1000',
            ),
        );

        Http::assertNothingSent();
    }
}
