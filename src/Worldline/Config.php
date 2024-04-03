<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline;

use POM\iDEAL\Banks\BankInterface;

readonly class Config
{
    public function __construct(
        private string $merchantId,
        private bool $testMode,
        private string $merchantCertificate,
        private string $merchantKey,
        private string $merchantPassphrase,
        private string $acquirerCertificate,
        private BankInterface $bank,
    )
    {
    }

    /**
     * @return string
     */
    public function getMerchantKey(): string
    {
        return $this->merchantKey;
    }

    /**
     * @return string
     */
    public function getAcquirerCertificate(): string
    {
        return $this->acquirerCertificate;
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
     * @return BankInterface
     */
    public function getBank(): BankInterface
    {
        return $this->bank;
    }

    public function getMerchantCertificate(): string
    {
        return $this->merchantCertificate;
    }

    public function getMerchantPassphrase(): string
    {
        return $this->merchantPassphrase;
    }

}