<?php declare(strict_types=1);

namespace POM\iDEAL\Hub;

use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use POM\iDEAL\Banks\BankInterface;

readonly class Config
{
    private string $hubBaseUrl;

    /**
     * @param string $merchantId
     * @param bool $testMode
     * @param string $INGBaseUrl
     * @param string $INGmTLSCertificatePath
     * @param string $INGmTLSKeyPath
     * @param string $INGmTLSPassPhrase
     * @param OpenSSLAsymmetricKey|OpenSSLCertificate|string $INGSigningKey
     * @param string $INGSigningPassphrase
     * @param string $INGSigningCertificate The signing certificate for requests to the ING API in DER format
     * @param string $hubmTLSCertificatePath
     * @param string $hubmTLSKeyPath
     * @param string $hubmTLSPassphrase
     * @param OpenSSLAsymmetricKey|OpenSSLCertificate|string $hubSigningKey
     * @param string $hubSigningCertificate The signing certificate for requests to the Currence iDEAL hub in DER format
     * @param string $hubSigningPassphrase
     * @param SigningAlgorithm $signingAlgorithm
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
    ) {
        if ($this->testMode) {
            $this->hubBaseUrl = 'https://merchant-cpsp-mtls.ext.idealapi.nl';
        } else {
            $this->hubBaseUrl = 'https://merchant-cpsp-mtls.idealapi.nl';
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

    public function getHubSigningPassphrase(): string
    {
        return $this->hubSigningPassphrase;
    }

    public function getAcquirerBaseUrl(): string
    {
        return $this->bank->getBaseUrl();
    }

    public function getHubBaseUrl(): string
    {
        return $this->hubBaseUrl;
    }
}