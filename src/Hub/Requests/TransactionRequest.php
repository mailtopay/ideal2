<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Requests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use POM\iDEAL\Hub\iDEAL;
use POM\iDEAL\Hub\Resources\AccessToken;
use POM\iDEAL\Hub\Resources\HubSignature;
use POM\iDEAL\Hub\Resources\TransactionResponse;

readonly class TransactionRequest
{
    private HubSignature $hubSignature;

    /**
     * @param iDEAL $iDEAL
     * @param AccessToken $accessToken
     * @param string $tokenRequestId
     */
    public function __construct(private iDEAL $iDEAL, private AccessToken $accessToken, private string $requestId)
    {
        $signingKey = openssl_pkey_get_private(
            $this->iDEAL->getConfig()->getHubSigningKey(),
            $this->iDEAL->getConfig()->getHubSigningPassphrase(),
        );

        $this->hubSignature = new HubSignature(
            $this->iDEAL->getConfig()->getHubSigningCertificate(),
            $signingKey,
            $this->iDEAL->getConfig()->getSigningAlgorithm(),
            $this->iDEAL->getConfig()->getMerchantId(),
            $this->accessToken->getId(),
            $requestId
        );
    }

    public function execute(int $amount, string $description, string $reference, string $returnUrl): TransactionResponse
    {
        $client = new Client();

        $body = [
            'amount' => [
                'amount' => $amount,
                'type'  => 'FIXED',
                'currency' => 'EUR'
            ],
            'creditor' => [
                "countryCode" => "NL",
            ],
            'returnUrl' => $returnUrl,
            'description' => $description,
            'reference' => $reference,
        ];

        $endpoint = '/v2/merchant-cpsp/transactions';

        $headers = [
            'Request-ID' => $this->requestId,
            'Signature' => $this->hubSignature->getSignature($body, $endpoint),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. $this->accessToken->getToken()
        ];

        $request = new Request(
            'POST',
            $this->iDEAL->getConfig()->getHubBaseUrl().$endpoint,
            $headers,
            json_encode($body, JSON_UNESCAPED_SLASHES),
        );

        $options = [
            'cert'      => [$this->iDEAL->getConfig()->getHubmTLSCertificatePath(), $this->iDEAL->getConfig()->getHubmTLSPassphrase()],
            'ssl_key'   => [$this->iDEAL->getConfig()->getHubmTLSKeyPath(), $this->iDEAL->getConfig()->getHubmTLSPassphrase()],
        ];

        $response = $client->send($request, $options);

        $data = json_decode($response->getBody()->getContents(), true);

        return TransactionResponse::fromArray($data);
    }
}