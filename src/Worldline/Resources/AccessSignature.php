<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Resources;

use DateTime;
use POM\iDEAL\Worldline\iDEAL;

class AccessSignature
{
    private array $headers;
    public function __construct(
        private iDEAL $iDEAL,
        private DateTime $dateTime,
    ) {
        $this->headers = [
            'app' => $this->iDEAL->getConfig()->getBank()->getApp(),
            'client' => $this->iDEAL->getConfig()->getBank()->getClient(),
            'date' => $this->dateTime->format(DATE_ATOM),
            'id' => $this->iDEAL->getConfig()->getMerchantId(),
        ];
    }

    /**
     * Get a signature based on the headers
     *
     * @return string
     */
    public function getSignature(): string
    {
        $privateKey = openssl_pkey_get_private(file_get_contents($this->iDEAL->getConfig()->getBankKey()), '');

        if (false === $privateKey) {
            throw new \Exception('Could not get private key: ' . esc_html((string) openssl_error_string()));
        }

        $headerPieces = [];

        foreach ($this->headers as $name => $value) {
            $headerPieces[] = $name . ': ' . $value;
        }

        $result = openssl_sign(implode("\n", $headerPieces), $signature, $privateKey, 'sha256WithRSAEncryption');

        if (false === $result) {
            throw new \Exception('Could not sign: ' . esc_html((string) openssl_error_string()));
        }

        $signatureResult =  sprintf(
            'Signature keyId="%s", algorithm="SHA256withRSA", headers="%s", signature="%s"',
            openssl_x509_fingerprint(file_get_contents($this->iDEAL->getConfig()->getBankCertificate())),
            implode(' ', array_keys($headerPieces)),
            base64_encode($signature)
        );

        return $signatureResult;
    }
}