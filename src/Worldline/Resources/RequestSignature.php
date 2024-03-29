<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Resources;

use DateTime;
use POM\iDEAL\Worldline\iDEAL;
use Ramsey\Uuid\UuidInterface;

class RequestSignature
{
    private array $headers;
    public function __construct(
        private iDEAL $iDEAL,
        private DateTime $dateTime,
        private UuidInterface $uuid,
        private string $encodedBody,
        private string $endpoint,
    ) {
        $this->headers = [
            'digest' => $encodedBody,
            'x-request-id' => $uuid->toString(),
            'messagecreatedatetime' => $this->dateTime->format(DATE_ATOM),
            '(request-target)' => 'post '. strtolower($endpoint),
        ];
    }

    /**
     * Get a signature based on the headers
     *
     * @return string
     */
    public function getSignature(): string
    {
        $privateKey = openssl_pkey_get_private($this->iDEAL->getConfig()->getBankKey(), '');

        if (false === $privateKey) {
            throw new \Exception('Could not get private key: ' . esc_html((string) openssl_error_string()));
        }

        $headerPieces = [];

        foreach ($this->headers as $name => $value) {
            $headerPieces[] = $name . ': ' . $value;
        }

        $headerPieces = implode("\n", $headerPieces);

        $stringToSign = $headerPieces;

        $result = openssl_sign($stringToSign, $signature, $privateKey, 'sha256WithRSAEncryption');

        if (false === $result) {
            throw new \Exception('Could not sign: ' . esc_html((string) openssl_error_string()));
        }

        $signatureResult =  sprintf(
            'Signature keyId="%s", algorithm="SHA256withRSA", headers="%s", signature="%s"',
            openssl_x509_fingerprint($this->iDEAL->getConfig()->getBankCertificate()),
            implode(' ', array_keys($this->headers)),
            base64_encode($signature)
        );

        return $signatureResult;
    }
}