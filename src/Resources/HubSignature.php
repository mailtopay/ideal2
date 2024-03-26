<?php

namespace POM\iDeal\Resources;

use Firebase\JWT\JWT;
use POM\iDeal\iDEAL;

class HubSignature
{
    private array $headers;
    public function __construct(
        private readonly iDEAL $iDEAL,
        private readonly \OpenSSLAsymmetricKey $signingKey,
        private string $signingCertificate,
        string $tokenRequestId
    ) {
        var_dump($this->calculateDigest());

        $this->headers = [
            'typ' => 'jose+json',
            'x5c' => [$this->calculateDigest()],
            'alg' => 'ES256',
            'https://idealapi.nl/sub' => $this->iDEAL->getMerchantId(),
            'https://idealapi.nl/iss' => $this->iDEAL->getMerchantId(),
            'https://idealapi.nl/scope' => 'MERCHANT',
            'https://idealapi.nl/acq' => substr($this->iDEAL->getMerchantId(),0, 4),
            'https://idealapi.nl/iat' => date('Y-m-d\TH:i:s.000\Z'),
            'https://idealapi.nl/jti' => '3bdf6416-db1c-4d0f-80fb-e3a948122780',
            'https://idealapi.nl/token-jti' => $tokenRequestId,
            'crit' => ["https://idealapi.nl/sub", "https://idealapi.nl/iss", "https://idealapi.nl/acq", "https://idealapi.nl/iat", "https://idealapi.nl/jti", "https://idealapi.nl/path", "https://idealapi.nl/scope", "https://idealapi.nl/token-jti"],
        ];
    }

    public function getSignature(array $payload, string $path): string
    {
        $headers = $this->headers;

        $headers['https://idealapi.nl/path'] = $path;

        $jwt = JWT::encode($payload, $this->signingKey, 'ES256', null, $headers);

        $jwt = explode('.', $jwt);

        return $jwt[0] . '..' . $jwt[2];
    }

    private function calculateDigest(): string
    {
        return base64_encode($this->signingCertificate);
    }
}