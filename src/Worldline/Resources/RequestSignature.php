<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Resources;

use DateTime;
use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Worldline\iDEAL;

class RequestSignature
{
    private array $headers;
    public function __construct(
        private readonly iDEAL $iDEAL,
        DateTime $dateTime,
        string $requestId,
    ) {
        $this->headers = [
            'x-request-id' => $requestId,
            'messagecreatedatetime' => $dateTime->format(DATE_ATOM),
        ];
    }

    /**
     * Get a signature based on the headers
     *
     * @return string
     * @throws IDEALException
     */
    public function getSignature(string|array $body, string $httpMethod, string $endpoint): string
    {
        $privateKey = openssl_pkey_get_private($this->iDEAL->getConfig()->getMerchantKey(), $this->iDEAL->getConfig()->getMerchantPassphrase());

        if (false === $privateKey) {
            throw new IDEALException('Could not get private key: ' . openssl_error_string());
        }

        $headers = [
            '(request-target)' => strtolower($httpMethod) . ' ' . strtolower($endpoint),
        ];

        // dont include the digest header for empty body
        if (!empty($body)) {
            $headers['digest'] = 'SHA-256='.base64_encode(hash('sha256', json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), true));
        }

        $headers = array_merge($this->headers, $headers);

        $headerPieces = [];

        foreach ($headers as $name => $value) {
            $headerPieces[] = $name . ': ' . $value;
        }

        $headerPieces = implode("\n", $headerPieces);

        $result = openssl_sign($headerPieces, $signature, $privateKey, 'sha256WithRSAEncryption');

        if ($result === false) {
            throw new IDEALException('Could not sign: ' . openssl_error_string());
        }

        return sprintf(
            'Signature keyId="%s", algorithm="SHA256withRSA", headers="%s", signature="%s"',
            openssl_x509_fingerprint($this->iDEAL->getConfig()->getMerchantCertificate()),
            implode(' ', array_keys($headers)),
            base64_encode($signature)
        );
    }
}