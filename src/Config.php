<?php declare(strict_types=1);

namespace POM\iDEAL;

readonly class Config
{
    public function __construct(
        private string $merchantId,
        private bool $testMode,
        private string $baseUrl,
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
}