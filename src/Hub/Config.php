<?php declare(strict_types=1);

namespace POM\iDEAL\Hub;

use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use POM\iDEAL\Banks\BankInterface;
use Psr\SimpleCache\CacheInterface;

readonly class Config
{
    private string $hubBaseUrl;
    private string $cachePrefix;

    /**
     * @param string $merchantId
     * @param bool $testMode
     * @param BankInterface $bank
     * @param string $INGmTLSCertificatePath
     * @param string $INGmTLSKeyPath
     * @param string $INGmTLSPassPhrase
     * @param OpenSSLAsymmetricKey|OpenSSLCertificate|string $INGSigningKey
     * @param string $INGSigningPassphrase
     * @param string $INGSigningCertificate The signing certificate for requests to the ING API in PEM format
     * @param string $hubmTLSCertificatePath
     * @param string $hubmTLSKeyPath
     * @param string $hubmTLSPassphrase
     * @param OpenSSLAsymmetricKey|OpenSSLCertificate|string $hubSigningKey
     * @param string $hubSigningCertificate The signing certificate for requests to the Currence iDEAL hub in PEM format
     * @param string $hubSigningPassphrase The passphrase to decrypt the hub signing keys
     * @param SigningAlgorithm $signingAlgorithm
     * @param CacheInterface $cache Caching to store the hub signing certificates
     * @param string|null $cachePrefix Optional caching prefix default 'pom_ideal2_test' or 'pom_ideal2'
     */
    public function __construct(
        private string $merchantId,
        private bool $testMode,
        private BankInterface $bank,
        private string $INGmTLSCertificatePath,
        private string $INGmTLSKeyPath,
        private string $INGmTLSPassPhrase,
        private OpenSSLAsymmetricKey|OpenSSLCertificate|string $INGSigningKey,
        private string $INGSigningPassphrase,
        private string $INGSigningCertificate,
        private string $hubmTLSCertificatePath,
        private string $hubmTLSKeyPath,
        private string $hubmTLSPassphrase,
        private OpenSSLAsymmetricKey|OpenSSLCertificate|string $hubSigningKey,
        private string $hubSigningCertificate,
        private string $hubSigningPassphrase,
        private SigningAlgorithm $signingAlgorithm,
        private CacheInterface $cache,
        string $cachePrefix = null,
    ) {
        if ($this->testMode) {
            $this->hubBaseUrl = 'https://merchant-cpsp-mtls.ext.idealapi.nl';
        } else {
            $this->hubBaseUrl = 'https://merchant-cpsp-mtls.idealapi.nl';
        }

        if (!is_null($cachePrefix)) {
            $this->cachePrefix = $cachePrefix;
        } else {
            $this->cachePrefix = $this->testMode ? 'pom_ideal2_test' : 'pom_ideal2';
        }
    }

    /**
     * @return string
     */
    public function getINGmTLSCertificatePath(): string
    {
        return $this->INGmTLSCertificatePath;
    }

    /**
     * @return string
     */
    public function getINGmTLSKeyPath(): string
    {
        return $this->INGmTLSKeyPath;
    }

    /**
     * @return string
     */
    public function getHubmTLSCertificatePath(): string
    {
        return $this->hubmTLSCertificatePath;
    }

    /**
     * @return string
     */
    public function getHubmTLSKeyPath(): string
    {
        return $this->hubmTLSKeyPath;
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * @return string
     */
    public function getINGmTLSPassPhrase(): string
    {
        return $this->INGmTLSPassPhrase;
    }

    /**
     * @return OpenSSLAsymmetricKey|OpenSSLCertificate|string
     */
    public function getINGSigningKey(): OpenSSLAsymmetricKey|OpenSSLCertificate|string
    {
        return $this->INGSigningKey;
    }

    /**
     * @return string
     */
    public function getINGSigningPassphrase(): string
    {
        return $this->INGSigningPassphrase;
    }

    /**
     * @return string
     */
    public function getINGSigningCertificate(): string
    {
        return $this->INGSigningCertificate;
    }

    /**
     * @return string
     */
    public function getHubmTLSPassphrase(): string
    {
        return $this->hubmTLSPassphrase;
    }

    /**
     * @return OpenSSLAsymmetricKey|OpenSSLCertificate|string
     */
    public function getHubSigningKey(): OpenSSLAsymmetricKey|OpenSSLCertificate|string
    {
        return $this->hubSigningKey;
    }

    /**
     * @return string
     */
    public function getHubSigningCertificate(): string
    {
        return $this->hubSigningCertificate;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return SigningAlgorithm
     */
    public function getSigningAlgorithm(): SigningAlgorithm
    {
        return $this->signingAlgorithm;
    }

    /**
     * @return string
     */
    public function getHubSigningPassphrase(): string
    {
        return $this->hubSigningPassphrase;
    }

    /**
     * @return string
     */
    public function getAcquirerBaseUrl(): string
    {
        return $this->bank->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getHubBaseUrl(): string
    {
        return $this->hubBaseUrl;
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