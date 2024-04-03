<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Requests;

use DateInterval;
use DateTime;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use POM\iDEAL\Helpers\Encode;
use POM\iDEAL\Hub\iDEAL;
use POM\iDEAL\Hub\Resources\AccessToken;

readonly class AccessTokenRequest
{
    public function __construct(private iDEAL $iDEAL)
    {
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
                'client_id' => $this->iDEAL->getConfig()->getMerchantId(),
                'scope' => 'ideal2', // always ideal2
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer', // always the same
                'client_assertion' => $this->createJWT(),
            ],
            'cert'      => [$this->iDEAL->getConfig()->getINGmTLSCertificatePath(), $this->iDEAL->getConfig()->getINGmTLSPassPhrase()],
            'ssl_key'   => [$this->iDEAL->getConfig()->getINGmTLSKeyPath(), $this->iDEAL->getConfig()->getINGmTLSPassPhrase()],
        ];

        $request  = new Request('POST', $this->iDEAL->getConfig()->getAcquirerBaseUrl() . '/ideal2/merchanttoken', $headers);

        $response = $client->send($request, $options);

        $responseBody = $response->getBody()->getContents();

        $response = json_decode($responseBody);

        $expireDateTime = new DateTime();
        $expireDateTime->add(new DateInterval('PT' . $response->expires_in . 'S'));

        // Decode headers from the JWT string WITHOUT validation
        // **IMPORTANT**: This operation is vulnerable to attacks, as the JWT has not yet been verified.
        // These headers could be any value sent by an attacker.
        list($headersB64, $payloadB64, $sig) = explode('.', $response->access_token);

//        var_dump(base64_decode($headersB64));
//        var_dump(base64_decode($payloadB64));
//        exit;

//        $payload = JWT::decode(
//            $response->access_token,
//            new Key(file_get_contents('../certificates/signing-hub-sandbox.pem'),
//            $this->iDEAL->getSigningAlgorithm())
//        );

        $payload = json_decode(base64_decode($payloadB64), true);

        return new AccessToken($response->access_token, $expireDateTime, $payload['jti']);
    }

    /**
     * @return string
     */
    private function createJWT(): string
    {
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
            $this->iDEAL->getConfig()->getINGSigningKey(),
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