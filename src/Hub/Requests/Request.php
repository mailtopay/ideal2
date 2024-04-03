<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Requests;

use GuzzleHttp\Client;
use POM\iDEAL\Hub\iDEAL;
use POM\iDEAL\Hub\Resources\AccessToken;
use POM\iDEAL\Hub\Resources\HubSignature;
use Ramsey\Uuid\Uuid;

class Request
{
    private string $requestId;
    private HubSignature $hubSignature;
    protected string $endpoint;
    protected string|array $body = '';
    protected string $requestMethod;
    private Client $client;
    /**
     * @var array|array[]
     */
    private array $options;
    private array $headers;

    /**
     * @param iDEAL $iDEAL
     * @param AccessToken $accessToken
     * @throws \Exception
     */
    public function __construct(private readonly iDEAL $iDEAL, private readonly AccessToken $accessToken)
    {
        // prepare signing key
        $signingKey = openssl_pkey_get_private(
            $this->iDEAL->getConfig()->getHubSigningKey(),
            $this->iDEAL->getConfig()->getHubSigningPassphrase(),
        );

        // generate new
        $this->requestId = (Uuid::uuid4())->toString();

        // setup hub signer
        $this->hubSignature = new HubSignature(
            $this->iDEAL->getConfig()->getHubSigningCertificate(),
            $signingKey,
            $this->iDEAL->getConfig()->getSigningAlgorithm(),
            $this->iDEAL->getConfig()->getMerchantId(),
            $this->accessToken->getId(),
            $this->requestId,
        );

        // setup guzzle client
        $this->client = new Client([
            'base_uri' => $this->iDEAL->getConfig()->getHubBaseUrl(),
        ]);

        $this->options = [
            'cert'      => [$this->iDEAL->getConfig()->getHubmTLSCertificatePath(), $this->iDEAL->getConfig()->getHubmTLSPassphrase()],
            'ssl_key'   => [$this->iDEAL->getConfig()->getHubmTLSKeyPath(), $this->iDEAL->getConfig()->getHubmTLSPassphrase()],
        ];

        $this->headers = [
            'Request-ID' => $this->requestId,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. $this->accessToken->getToken(),
        ];

    }

    protected function send(): array
    {
        $signatureHeader = [
            'Signature' => $this->hubSignature->getSignature($this->body, $this->endpoint),
        ];

        $headers = array_merge($this->headers, $signatureHeader);

        $body = $this->body;

        if (is_array($body)) {
            $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $request = new \GuzzleHttp\Psr7\Request(
            $this->requestMethod,
            $this->endpoint,
            $headers,
            $body,
        );

        $response = $this->client->send($request, $this->options);

        return json_decode($response->getBody()->getContents(), true);
    }
}