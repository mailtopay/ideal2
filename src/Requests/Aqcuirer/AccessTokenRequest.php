<?php

namespace POM\iDeal\Requests\Aqcuirer;

use DateInterval;
use DateTime;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use POM\iDeal\Helpers\Encode;
use POM\iDeal\iDEAL;
use POM\iDeal\Resources\AccessToken;

readonly class AccessTokenRequest
{
    public function __construct(
        private iDEAL $iDEAL,
        private string $mtlsCertificate,
        private string $mtlsKey,
        private string $mtlsPassphrase,
        private string $signingCertificate,
        private \OpenSSLAsymmetricKey $signingKey
    ) {
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(): AccessToken
    {
        $client = new Client();

        $headers = [
            'Content-type' => 'application/x-www-form-urlencoded',
        ];

        $options = [
            'form_params' => [
                'grant_type' => 'client_credentials', // always client_credentials
                'client_id' => $this->iDEAL->getMerchantId(),
                'scope' => 'ideal2', // always ideal2
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer', // always the same
                'client_assertion' => $this->createJWT(),
            ],
            'cert'      => [$this->mtlsCertificate, $this->mtlsPassphrase],
            'ssl_key'   => [$this->mtlsKey, $this->mtlsPassphrase],
        ];

        $request  = new Request('POST', $this->iDEAL->getBankBaseUrl() . '/ideal2/merchanttoken', $headers);

        $response = $client->send($request, $options);

        $response = json_decode($response->getBody()->getContents());

        $expireDateTime = new DateTime();
        $expireDateTime->add(new DateInterval('PT' . $response->expires_in . 'S'));

        // Decode headers from the JWT string WITHOUT validation
        // **IMPORTANT**: This operation is vulnerable to attacks, as the JWT has not yet been verified.
        // These headers could be any value sent by an attacker.
        list($headersB64, $payloadB64, $sig) = explode('.', $response->access_token);

        $payload = json_decode(base64_decode($payloadB64), true);

        return new AccessToken($response->access_token, $expireDateTime, $payload['jti']);
    }

    /**
     * @return string
     */
    private function createJWT(): string
    {
        $payload = [
            "iss" => $this->iDEAL->getMerchantId(),
            "sub" => $this->iDEAL->getMerchantId(),
            "aud" => $this->iDEAL->getBankBaseUrl(),
            "iat" => time(),
        ];

        $headers = [
            'alg' => $this->iDEAL->getSigningAlgorithm(),
            'typ' => 'JWT',    // JWT type
            'x5t#S256' => $this->calculateDigest(),
        ];

        return JWT::encode($payload, $this->signingKey, $this->iDEAL->getSigningAlgorithm(), null, $headers);
    }

    private function calculateDigest(): string
    {
        $sha256Digest = hash('sha256', $this->signingCertificate, true);

        // Encode the SHA256 digest using Base64url encoding
        $encode = new Encode();

        return $encode->base64UrlEncode($sha256Digest);
    }
}