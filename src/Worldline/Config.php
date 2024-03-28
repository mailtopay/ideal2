<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline;

readonly class Config
{
    public function __construct(
        private string $merchantId,
        private bool $testMode,
        private string $baseUrl,
        private string $bankCertificate,
        private string $bankKey,
        private string $tppCertificate,
    )
    {
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getBankCertificate(): string
    {
        return $this->bankCertificate;
    }

    /**
     * @return string
     */
    public function getBankKey(): string
    {
        return $this->bankKey;
    }

    /**
     * @return string
     */
    public function getTppCertificate(): string
    {
        return $this->tppCertificate;
    }

}