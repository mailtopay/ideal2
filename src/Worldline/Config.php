<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline;

use POM\iDEAL\Banks\BankBase;

readonly class Config
{
    public function __construct(
        private string $merchantId,
        private bool $testMode,
        private string $baseUrl,
        private string $bankCertificate,
        private string $bankKey,
        private string $tppCertificate,
        private BankBase $bank,
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

    /**
     * @return BankBase
     */
    public function getBank(): BankBase
    {
        return $this->bank;
    }


}