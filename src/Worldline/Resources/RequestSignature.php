<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Resources;

use DateTime;
use Exception;
use POM\iDEAL\Worldline\iDEAL;
use Ramsey\Uuid\UuidInterface;

class RequestSignature
{
    private array $headers;
    public function __construct(
        private readonly iDEAL $iDEAL,
        DateTime $dateTime,
        UuidInterface $uuid,
        string $encodedBody,
        string $httpMethod,
        string $endpoint,
    ) {
        $this->headers = [
            'digest' => $encodedBody,
            'x-request-id' => $uuid->toString(),
            'messagecreatedatetime' => $dateTime->format(DATE_ATOM),
            '(request-target)' => strtolower($httpMethod) . ' ' . strtolower($endpoint),
        ];
    }

    /**
     * Get a signature based on the headers
     *
     * @return string
     * @throws Exception
     */
    public function getSignature(): string
    {
        $privateKey = openssl_pkey_get_private($this->iDEAL->getConfig()->getMerchantKey(), $this->iDEAL->getConfig()->getMerchantPassphrase());

        if (false === $privateKey) {
            throw new Exception('Could not get private key: ' . openssl_error_string());
        }

        $headerPieces = [];

        foreach ($this->headers as $name => $value) {
            $headerPieces[] = $name . ': ' . $value;
        }

        $headerPieces = implode("\n", $headerPieces);

        $stringToSign = $headerPieces;

        $result = openssl_sign($stringToSign, $signature, $privateKey, 'sha256WithRSAEncryption');

        if ($result === false) {
            throw new Exception('Could not sign: ' . openssl_error_string());
        }

        return sprintf(
            'Signature keyId="%s", algorithm="SHA256withRSA", headers="%s", signature="%s"',
            openssl_x509_fingerprint($this->iDEAL->getConfig()->getMerchantCertificate()),
            implode(' ', array_keys($this->headers)),
            base64_encode($signature)
        );
    }
}