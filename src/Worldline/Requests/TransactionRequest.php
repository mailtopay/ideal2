<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Requests;

use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use POM\iDEAL\Worldline\iDEAL;
use POM\iDEAL\Worldline\Resources\AccessToken;
use POM\iDEAL\Worldline\Resources\RequestSignature;
use POM\iDEAL\Worldline\Resources\TransactionResponse;
use Ramsey\Uuid\Uuid;

readonly class TransactionRequest
{
    private RequestSignature $requestSignature;

    private \OpenSSLAsymmetricKey $signingKey;

    /**
     * @param iDEAL $iDEAL
     * @param AccessToken $accessToken
     */
    public function __construct(private iDEAL $iDEAL, private AccessToken $accessToken)
    {
        $this->signingKey = openssl_pkey_get_private(
            $this->iDEAL->getConfig()->getBankKey(),
            ''
        );
    }

    public function execute(string $amount, string $reference): TransactionResponse
    {
        $client = new Client();

        $endpoint = '/xs2a/routingservice/services/ob/pis/v3/payments';
        $body = [
            'PaymentProduct'    => [
                'IDEAL',
            ],
            'CommonPaymentData' => [
                'Amount' => [
                    'Type' => 'Fixed',
                    'Amount' => $amount,
                    'Currency' => 'EUR'
                ],
                'RemittanceInformation' => 'iDEAL | POM',
                'RemittanceInformationStructured' => [
                    'Reference' => $reference
                ]
            ],
            'IDEALPayments'     => [
                'UseDebtorToken' => false,
                'FlowType'       => 'Standard',
            ],
        ];

        $dateTime = new DateTime('now', new DateTimeZone('UTC'));

        // Get the offset for the specified timezone (here assuming CET/CEST)
        $timezone = new DateTimeZone('Europe/Paris');
        $offset = $timezone->getOffset($dateTime);
        $dateTime->modify('+' . $offset . ' seconds');

        $uuid = Uuid::uuid4();
        $encodedBody = 'SHA-256='.base64_encode(hash('sha256', json_encode($body), true));
        $requestSignature = new RequestSignature($this->iDEAL, $dateTime, $uuid, $encodedBody, $endpoint);

        $headers = [
            'Digest' => $encodedBody,
            'X-Request-ID' => $uuid->toString(),
            'MessageCreateDateTime' => $dateTime->format(DATE_ATOM),
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken->getToken(),
            'Content-Type' => 'application/json',
            'Signature' => $requestSignature->getSignature(),
        ];

        $request = new Request(
            'POST',
            $this->iDEAL->getConfig()->getBaseUrl() . $endpoint,
            $headers,
            json_encode($body)
        );

        $response = $client->send($request);
        $data = json_decode($response->getBody()->getContents(), true);

        return TransactionResponse::fromArray($data);
    }
}