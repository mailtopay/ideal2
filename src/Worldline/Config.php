<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline;

use POM\iDEAL\Banks\BankInterface;

readonly class Config
{
    public function __construct(
        private string $merchantId,
        private bool $testMode,
        private string $bankCertificate,
        private string $bankKey,
        private string $tppCertificate,
        private BankInterface $bank,
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
        return $this->bank->getBaseUrl();
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
     * @return BankInterface
     */
    public function getBank(): BankInterface
    {
        return $this->bank;
    }

}