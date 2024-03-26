<?php

namespace POM\iDeal;

use OpenSSLAsymmetricKey;
use POM\iDeal\Requests\Aqcuirer\AccessTokenRequest;

class iDEAL
{
    private string $bankBaseUrl;

    const PROD_URL = 'https://merchant-cpsp-mtls.idealapi.nl/v2';
    const TEST_URL = 'https://merchant-cpsp-mtls.ext.idealapi.nl/v2';

    /**
     * @param string $merchantId
     * @param Bank $bank,
     * @param string $certificateFilePath
     * @param string $privateKeyFilePath
     * @param bool $sandbox
     */
    public function __construct(
        private string  $merchantId,
        Bank    $bank,
        private string $signingCertificate,
        private string $signingKey,
        private string $signingAlgorithm
    ) {
        $this->bankBaseUrl = $bank->value;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getBankBaseUrl(): string
    {
        return $this->bankBaseUrl;
    }

    public function createAccessTokenRequest(
        string $mtlsCertificatePath,
        string $mtlsKeyPath,
        string $mtlsPassPhrase,
        string $signingCertificate,
        OpenSSLAsymmetricKey $signingKey,
        string $accessTokenId
    ): AccessTokenRequest
    {
        return new AccessTokenRequest(
            $this,
            $mtlsCertificatePath,
            $mtlsKeyPath,
            $mtlsPassPhrase,
            $signingCertificate,
            $signingKey,
            $accessTokenId
        );
    }

    public function getSigningAlgorithm(): string
    {
        return $this->signingAlgorithm;
    }

    public function getSigningCertificate(): string
    {
        return $this->signingCertificate;
    }
}
