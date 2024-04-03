<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline;

use POM\iDEAL\Worldline\Requests\AccessTokenRequest;
use POM\iDEAL\Worldline\Requests\TransactionRequest;
use POM\iDEAL\Worldline\Resources\AccessToken;

readonly class iDEAL
{
    /**
     * @param Config $config
     */
    public function __construct(private Config $config)
    {
    }

    public function createAccessTokenRequest(): AccessTokenRequest
    {
        return new AccessTokenRequest($this);
    }

    public function createTransactionRequest(AccessToken $accessToken): TransactionRequest
    {
        return new TransactionRequest(
            $this,
            $accessToken
        );
    }

    public function verifyCallbackResponse(string $callbackResponse, array $headers = null): bool
    {
        // Decode the JSON string
        $data = json_decode($callbackResponse);

        // Encode the data back to JSON without formatting
        $plainJson          = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $calculatedDigest   = 'SHA-256=' . base64_encode(hash('sha256', $plainJson, true));

        // get headers
        if (is_null($headers)) {
            $digest     = $_SERVER['HTTP_Digest'];
            $signature  = $_SERVER['HTTP_Signature'];
            $requestId  = $_SERVER['HTTP_X-Request-ID'];
            $dateTime   = $_SERVER['HTTP_MessageCreateDateTime'];
        } else {
            $digest     = $headers['HTTP_Digest'];
            $signature  = $headers['HTTP_Signature'];
            $requestId  = $headers['HTTP_X-Request-ID'];
            $dateTime   = $headers['HTTP_MessageCreateDateTime'];
        }

        // Check if digest is the same as the one given in the header by Worldline
        if ($digest !== $calculatedDigest) {
            return false;
        }

        $headersGiven = "messagecreatedatetime: $dateTime
x-request-id: $requestId
digest: $digest";

        // Get the signature
        $items = explode(',', $signature);
        $signature = str_replace(['signature="', '"', ' '], '', $items[3]);

        // Signature to be verified
        $signature = base64_decode($signature);

        // verify response
        $verified = openssl_verify($headersGiven, $signature, $this->config->getAcquirerCertificate(), OPENSSL_ALGO_SHA256);

        return $verified === 1;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}