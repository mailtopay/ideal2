<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline;

use POM\iDEAL\Banks\BankInterface;
use Psr\SimpleCache\CacheInterface;

readonly class Config
{

    private string $cachePrefix;

    /**
     * @param string $merchantId
     * @param bool $testMode
     * @param string $merchantCertificate
     * @param string $merchantKey
     * @param string $merchantPassphrase
     * @param string $acquirerCertificate
     * @param BankInterface $bank
     * @param string $notificationUrl
     * @param CacheInterface $cache
     * @param string $cachePrefix
     */
    public function __construct(
        private string $merchantId,
        private bool $testMode,
        private string $merchantCertificate,
        private string $merchantKey,
        private string $merchantPassphrase,
        private string $acquirerCertificate,
        private BankInterface $bank,
        private string $notificationUrl,
        private CacheInterface $cache,
        ?string $cachePrefix = null,
    )
    {
        if (!is_null($cachePrefix)) {
            $this->cachePrefix = $cachePrefix;
        } else {
            $this->cachePrefix = $this->testMode ? 'pom_ideal2_test_' : 'pom_ideal2_';
        }
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

    public function getNotificationUrl(): string
    {
        return $this->notificationUrl;
    }

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getCachePrefix(): string
    {
        return $this->cachePrefix;
    }

}