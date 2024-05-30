<?php

namespace POM\iDEAL\Worldline\Requests;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Worldline\iDEAL;
use POM\iDEAL\Worldline\Resources\RequestSignature;
use Ramsey\Uuid\Uuid;

class Request
{
    private Client $client;
    protected string $endpoint;
    protected string|array $body = '';
    protected string $requestMethod;
    private string $requestId;
    private DateTime $messageDatetime;
    private RequestSignature $requestSignature;
    private array $headers;

    /**
     * @throws IDEALException
     */
    public function __construct(protected iDEAL $iDEAL)
    {
        $this->client = new Client([
            'base_uri' => $this->iDEAL->getConfig()->getBaseUrl(),
        ]);

        try {
            $this->requestId = (Uuid::uuid4())->toString();
        } catch (\Exception $e) {
            throw new IDEALException('Could not create uuid: ' . $e->getMessage());
        }
        $this->messageDatetime = new DateTime();

        $this->requestSignature = new RequestSignature(
            $this->iDEAL,
            $this->messageDatetime,
            $this->requestId
        );

        $this->headers = [
            'X-Request-ID' => $this->requestId,
            'MessageCreateDateTime' => $this->messageDatetime->format(DATE_ATOM),
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->iDEAL->getAccessToken()->getToken(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * @throws IDEALException
     */
    protected function send(): array
    {
        try {
            $headers = [
                'Signature' => $this->requestSignature->getSignature($this->body, $this->requestMethod, $this->endpoint),
            ];
        } catch (\Exception $e) {
            throw new IDEALException('Could not create signature: ' . $e->getMessage());
        }

        $headers = array_merge($this->headers, $headers);

        $body = $this->body;

        if (is_array($body)) {
            $body = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // dont include the digest header for empty body
        if (!empty($body)) {
            $headers['Digest'] = 'SHA-256='.base64_encode(hash('sha256', $body, true));
        }

        $request = new \GuzzleHttp\Psr7\Request(
            $this->requestMethod,
            $this->endpoint,
            $headers,
            $body,
        );

        try {
            $response = $this->client->send($request);
        } catch (GuzzleException $e) {
            throw new IDEALException('Could not send request: ' . $e->getMessage());
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function setHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }
}