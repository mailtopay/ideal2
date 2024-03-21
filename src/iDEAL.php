<?php

namespace POM\iDeal;

use DateTime;

class iDEAL
{
    private string $bankBaseUrl;
    private string $merchantId;

    private string $hubToken;

    private DateTime $validUntil;

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
        string  $merchantId,
        Bank    $bank,
    )
    {
        $this->bankBaseUrl = $bank->value;
        $this->merchantId = $merchantId;
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

    /**
     * @return DateTime
     */
    public function getValidUntil(): DateTime
    {
        return $this->validUntil;
    }

    /**
     * @param DateTime $validUntil
     */
    public function setValidUntil(DateTime $validUntil): void
    {
        $this->validUntil = $validUntil;
    }

    /**
     * @return string
     */
    public function getHubToken(): string
    {
        return $this->hubToken;
    }

    /**
     * @param $hubToken
     * @return void
     */
    public function setHubToken($hubToken): void
    {
        $this->hubToken = $hubToken;
    }
}
