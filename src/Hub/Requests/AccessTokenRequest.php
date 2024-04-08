<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Requests;

use DateInterval;
use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Helpers\Encode;
use POM\iDEAL\Hub\iDEAL;
use POM\iDEAL\Hub\Resources\AccessToken;

readonly class AccessTokenRequest
{
    public function __construct(private iDEAL $iDEAL)
    {
    }

    /**
     * Retrieves an access token from the ING API
     *
     * @return AccessToken
     * @throws IDEALException
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
                'client_id' => $this->iDEAL->getConfig()->getMerchantId(),
                'scope' => 'ideal2', // always ideal2
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer', // always the same
                'client_assertion' => $this->createJWT(),
            ],
            'cert'      => [
                $this->iDEAL->getConfig()->getINGmTLSCertificatePath(),
                $this->iDEAL->getConfig()->getINGmTLSPassPhrase()
            ],
            'ssl_key'   => [
                $this->iDEAL->getConfig()->getINGmTLSKeyPath(),
                $this->iDEAL->getConfig()->getINGmTLSPassPhrase()
            ],
        ];

        $request  = new Request('POST', $this->iDEAL->getConfig()->getAcquirerBaseUrl() . '/ideal2/merchanttoken', $headers);

        try {
            $response = $client->send($request, $options);
        } catch (GuzzleException $e) {
            throw new IDEALException("Failed retrieving access token from ING: {$e->getMessage()}");
        }

        $response = json_decode($response->getBody()->getContents());

        $expires_in = $response->expires_in - 10;

        try {
            $interval = new DateInterval('PT' . $expires_in . 'S');
        } catch (Exception) {
            throw new IDEALException('Failed parsing token expiry');
        }

        list(,$payloadB64,) = explode('.', $response->access_token);

        $payload = json_decode(base64_decode($payloadB64), true);

        return new AccessToken($response->access_token, $payload['jti'], $interval);
    }

    /**
     * @return string Signed JWT token of the request
     */
    private function createJWT(): string
    {
        // prepare signing key
        $signingKey = openssl_pkey_get_private(
            $this->iDEAL->getConfig()->getINGSigningKey(),
            $this->iDEAL->getConfig()->getINGSigningPassphrase(),
        );

        $payload = [
            "iss" => $this->iDEAL->getConfig()->getMerchantId(),
            "sub" => $this->iDEAL->getConfig()->getMerchantId(),
            "aud" => $this->iDEAL->getConfig()->getAcquirerBaseUrl(),
            "iat" => time(),
        ];

        $headers = [
            'alg' => $this->iDEAL->getConfig()->getSigningAlgorithm()->value,
            'typ' => 'JWT',    // JWT type
            'x5t#S256' => $this->calculateDigest(),
        ];

        return JWT::encode(
            $payload,
            $signingKey,
            $this->iDEAL->getConfig()->getSigningAlgorithm()->value,
            null,
            $headers
        );
    }

    /**
     * Return an encoded digest of the signing certificate (x5t#S256)
     *
     * @return string
     */
    private function calculateDigest(): string
    {
        $sha256Digest = hash('sha256', $this->iDEAL->getConfig()->getINGSigningCertificate(), true);

        // Encode the SHA256 digest using Base64url encoding
        return Encode::base64UrlEncode($sha256Digest);
    }
}