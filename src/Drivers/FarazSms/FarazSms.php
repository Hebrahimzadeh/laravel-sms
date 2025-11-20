<?php

namespace Omalizadeh\SMS\Drivers\FarazSMS;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Omalizadeh\SMS\Drivers\Contracts\BulkSMSSender;
use Omalizadeh\SMS\Drivers\Contracts\Driver;
use Omalizadeh\SMS\Drivers\Contracts\TemplateSMSSender;
use Omalizadeh\SMS\Exceptions\InvalidSMSConfigurationException;
use Omalizadeh\SMS\Exceptions\SendingSMSFailedException;
use Omalizadeh\SMS\Requests\SendBulkSMSRequest;
use Omalizadeh\SMS\Requests\SendSMSRequest;
use Omalizadeh\SMS\Requests\SendTemplateSMSRequest;
use Omalizadeh\SMS\Responses\SendBulkSMSResponse;
use Omalizadeh\SMS\Responses\SendSMSResponse;

class FarazSMS extends Driver implements BulkSMSSender, TemplateSMSSender
{
    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     * @throws ConnectionException
     */
    public function send(SendSMSRequest $request): SendSMSResponse
    {
        $data = [
            'sending_type' => 'webservice',
            'from_number' => $request->getSender() ?: $this->getConfig('default_from_number'),
            'params' => [
                'recipients' => [$request->getPhoneNumber()],
            ],
            'message' => $request->getMessage(),
        ];

        $responseJson = $this->callApi($this->getSMSSendingURL(), $data);
        $responseData = $responseJson['data'];

        return new SendSMSResponse($responseData['message_outbox_ids'][0]);
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     * @throws ConnectionException
     */
    public function sendBulk(SendBulkSMSRequest $request): SendBulkSMSResponse
    {
        $data = [
            'sending_type' => 'webservice',
            'from_number' => $request->getSender() ?: $this->getConfig('default_from_number'),
            'params' => [
                'recipients' => $request->getPhoneNumbers(),
            ],
            'message' => $request->getMessage(),
        ];

        $responseJson = $this->callApi($this->getSMSSendingURL(), $data);
        $responseData = $responseJson['data'];

        return new SendBulkSMSResponse(
            array_map(
                fn(int $messageId) => new SendSMSResponse($messageId),
                $responseData['message_outbox_ids'],
            ),
        );
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     * @throws ConnectionException
     */
    public function sendTemplate(SendTemplateSMSRequest $request): SendSMSResponse
    {
        $template = $request->getTemplate();
        $template = is_string($template) ? $template : (string) $template;

        $data = [
            'sending_type' => 'pattern',
            'from_number' => $request->getSender() ?: $this->getConfig('default_from_number'),
            'code' => $template,
            'recipients' => [
                $request->getPhoneNumber(),
            ],
            'params' => $request->getParameters(),
        ];

        $responseJson = $this->callApi($this->getTemplateSMSSendingURL(), $data);
        $responseData = $responseJson['data'];

        return new SendSMSResponse($responseData['message_outbox_ids'][0]);
    }

    public function getSMSSendingURL(): string
    {
        return 'https://edge.ippanel.com/v1/api/send';
    }

    public function getBulkSMSSendingURL(): string
    {
        return $this->getSMSSendingURL();
    }

    public function getTemplateSMSSendingURL(): string
    {
        return $this->getSMSSendingURL();
    }

    /**
     * @throws InvalidSMSConfigurationException
     * @throws SendingSMSFailedException
     * @throws ConnectionException
     */
    protected function callApi(string $url, array $data)
    {
        if (empty($token = $this->getConfig('token'))) {
            throw new InvalidSMSConfigurationException('Invalid faraz_sms token.');
        }

        $response = Http::asJson()
            ->acceptJson()
            ->withHeader('Authorization', $token)
            ->post($url, $data);

        $responseJson = $response->json();

        if (!isset($responseJson['meta']['status'])) {
            throw new SendingSMSFailedException(
                'Invalid response from FarazSMS: ' . $response->body(),
                $response->status(),
            );
        }

        $status = $responseJson['meta']['status'];

        if ($status !== true) {
            throw new SendingSMSFailedException($responseJson['meta']['message'], $response->getStatusCode());
        }

        return $responseJson;
    }
}
