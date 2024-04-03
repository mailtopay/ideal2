<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Requests;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use POM\iDEAL\Worldline\iDEAL;
use POM\iDEAL\Worldline\Resources\AccessToken;
use POM\iDEAL\Worldline\Resources\RequestSignature;
use POM\iDEAL\Worldline\Resources\TransactionStatus;
use Ramsey\Uuid\Uuid;

readonly class TransactionStatusRequest
{
    /**
     * @param iDEAL $iDEAL
     * @param AccessToken $accessToken
     */
    public function __construct(private iDEAL $iDEAL, private AccessToken $accessToken)
    {
    }

    /**
     * @param string $transactionId
     * @return TransactionStatus
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(string $transactionId): TransactionStatus
    {
        $client = new Client();

        $endpoint = '/xs2a/routingservice/services/ob/pis/v3/payments/'. $transactionId .'/status';

        $dateTime = new DateTime('now');

        $requestId = Uuid::uuid4();

        $requestSignature = new RequestSignature(
            $this->iDEAL,
            $dateTime,
            $requestId,
            '',
            'GET',
            $endpoint
        );

        // set up the HTTP headers
        $headers = [
            'X-Request-ID' => $requestId->toString(),
            'MessageCreateDateTime' => $dateTime->format(DATE_ATOM),
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken->getToken(),
            'Content-Type' => 'application/json',
            'Signature' => $requestSignature->getSignature(),
        ];

        $request = new Request(
            'GET',
            $this->iDEAL->getConfig()->getBaseUrl() . $endpoint,
            $headers,
        );

        $response = $client->send($request);
        $data = json_decode($response->getBody()->getContents(), true);

        return TransactionStatus::fromArray($data);
    }
}