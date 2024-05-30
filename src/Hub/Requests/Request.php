<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Requests;

use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Helpers\DerConverter;
use POM\iDEAL\Hub\iDEAL;
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
     * @throws IDEALException
     */
    public function __construct(private readonly iDEAL $iDEAL)
    {
        // prepare signing key
        $signingKey = openssl_pkey_get_private(
            $this->iDEAL->getConfig()->getHubSigningKey(),
            $this->iDEAL->getConfig()->getHubSigningPassphrase(),
        );

        // generate new
        try {
            $this->requestId = (Uuid::uuid4())->toString();
        } catch (Exception) {
            throw new IDEALException('Failed generating request id, this shouldnt happen');
        }

        $derFormatted = DerConverter::pemToDer($this->iDEAL->getConfig()->getHubSigningCertificate());

        // setup hub signer
        $this->hubSignature = new HubSignature(
            $derFormatted,
            $signingKey,
            $this->iDEAL->getConfig()->getSigningAlgorithm(),
            $this->iDEAL->getConfig()->getMerchantId(),
            $this->iDEAL->getAccessToken()->getId(),
            $this->requestId,
        );

        // setup guzzle client
        $this->client = new Client([
            'base_uri' => $this->iDEAL->getConfig()->getHubBaseUrl(),
        ]);

        $this->options = [
            'cert'      => [
                $this->iDEAL->getConfig()->getHubmTLSCertificatePath(),
                $this->iDEAL->getConfig()->getHubmTLSPassphrase()
            ],
            'ssl_key'   => [
                $this->iDEAL->getConfig()->getHubmTLSKeyPath(),
                $this->iDEAL->getConfig()->getHubmTLSPassphrase()
            ],
        ];

        $this->headers = [
            'Request-ID' => $this->requestId,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. $this->iDEAL->getAccessToken()->getToken(),
        ];
    }

    /**
     * Sends the request to the Currence iDEAL hub
     *
     * @return array The JSON-decoded response body
     * @throws IDEALException
     */
    protected function send(): array
    {
        $signatureHeader = [
            'Signature' => $this->hubSignature->getSignature($this->body, $this->endpoint),
        ];

        $headers = array_merge($this->headers, $signatureHeader);

        $body = $this->body;

        // json encode the body if an array has been provided
        if (is_array($body)) {
            $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $request = new \GuzzleHttp\Psr7\Request(
            $this->requestMethod,
            $this->endpoint,
            $headers,
            $body,
        );

        try {
            $response = $this->client->send($request, $this->options);
        } catch (BadResponseException $e) {
            throw new IDEALException("Hub request failed: HTTP {$e->getResponse()->getStatusCode()} response: {$e->getResponse()->getBody()->getContents()}");
        } catch (GuzzleException $e) {
            throw new IDEALException("Hub request failed: {$e->getMessage()}");
        }

        $responseId = $response->getHeader('Request-Id');
        $responseId = $responseId[0] ?? '';

        if ($responseId !== $this->requestId) {
            throw new IDEALException("Response verification failure: request ID");
        }

        $responseSignature = $response->getHeader('Signature');
        $responseSignature = $responseSignature[0] ?? '';

        if (empty($responseSignature)) {
            throw new IDEALException("Response verification failure: No signature");
        }

        $responseBody = $response->getBody()->getContents();

        $responseSignature = str_replace('..', ".".JWT::urlsafeB64Encode($responseBody).".", $responseSignature);

        // decode the jwt to verify signature
        try {
            JWT::decode($responseSignature, $this->iDEAL->getCertificateStore()->getCertificates());
        }
        catch (Exception $e) {
            throw new IDEALException("Signature validation failed: {$e->getMessage()}");
        }

        return json_decode($responseBody, true);
    }
}