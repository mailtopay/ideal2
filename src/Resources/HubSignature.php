<?php declare(strict_types=1);

namespace POM\iDEAL\Resources;

use Firebase\JWT\JWT;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use POM\iDEAL\SigningAlgorithm;

class HubSignature
{
    private array $headers;
    public function __construct(
        string $signingCertificate,
        private readonly OpenSSLAsymmetricKey|OpenSSLCertificate|string $signingKey,
        private readonly SigningAlgorithm $signingAlgorithm,
        string $merchantId,
        string $tokenRequestId,
        string $requestId
    ) {
        $this->headers = [
            'typ' => 'jose+json',
            'x5c' => [base64_encode($signingCertificate)],
            'alg' => $signingAlgorithm->value,
            'https://idealapi.nl/sub' => $merchantId,
            'https://idealapi.nl/iss' => $merchantId,
            'https://idealapi.nl/scope' => 'MERCHANT',
            'https://idealapi.nl/acq' => substr($merchantId, 0, 4),
            'https://idealapi.nl/iat' => date('Y-m-d\TH:i:s.000\Z'),
            'https://idealapi.nl/jti' => $requestId,
            'https://idealapi.nl/token-jti' => $tokenRequestId,
            'crit' => [
                "https://idealapi.nl/sub",
                "https://idealapi.nl/iss",
                "https://idealapi.nl/acq",
                "https://idealapi.nl/iat",
                "https://idealapi.nl/jti",
                "https://idealapi.nl/path",
                "https://idealapi.nl/scope",
                "https://idealapi.nl/token-jti"
            ],
        ];
    }

    /**
     * Get a detached JWT from the request
     *
     * @param array $payload
     * @param string $path
     * @return string
     */
    public function getSignature(array $payload, string $path): string
    {
        // add the path to the headers array
        $headers = $this->headers;

        $headers['https://idealapi.nl/path'] = $path;

        // create the JWT
        $jwt = JWT::encode($payload, $this->signingKey, $this->signingAlgorithm->value, null, $headers);

        // remove the payload from the JWT to create a detached JWT
        $jwt = explode('.', $jwt);

        return $jwt[0] . '..' . $jwt[2];
    }
}