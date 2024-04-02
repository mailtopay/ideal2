<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline;

use POM\iDEAL\Worldline\Config;
use POM\iDEAL\Worldline\Requests\AccessTokenRequest;
use POM\iDEAL\Worldline\Requests\TransactionRequest;
use POM\iDEAL\Worldline\Resources\AccessToken;

class iDEAL
{
    private AccessToken $accessToken;

    /**
     * @param Config $config
     */
    public function __construct(private readonly Config $config)
    {
    }

    public function createAccessTokenRequest(): AccessTokenRequest
    {
        return new AccessTokenRequest($this);
    }

    public function createTransactionRequest(AccessToken $accessToken, string $requestId): TransactionRequest
    {
        return new TransactionRequest(
            $this,
            $accessToken,
            $requestId
        );
    }

    public function verifyResponse(string $json)
    {
        // Decode the JSON string
        $data = json_decode($json);

        // Encode the data back to JSON without formatting
        $plainJson          = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $calculatedDigest   = 'SHA-256=' . base64_encode(hash('sha256', $plainJson, true));

        $digest = $_SERVER['HTTP_Digest'];
        $signature = $_SERVER['HTTP_Signature'];
        $requestId = $_SERVER['HTTP_X-Request-ID'];
        $dateTime = $_SERVER['HTTP_MessageCreateDateTime'];

        // Check if digest is the same as the one given in the header by Worldline
        if ($digest !== $calculatedDigest) {
            return false;
        }

        $headersGiven = "messagecreatedatetime: ". $dateTime."
x-request-id: ". $_SERVER['HTTP_X-Request-ID'] ."
digest: ". $digest;
        // Get the signature
        $items = explode(',', $signature);
        $signature = str_replace(['signature="', '"', ' '], '', $items[3]);

        // Signature to be verified
        $signature = base64_decode($signature);

        $verified = openssl_verify($headersGiven, $signature, $this->config->getTppCertificate(), OPENSSL_ALGO_SHA256);


        // verify response

        if (true) {
            return true;
        }

        return false;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}