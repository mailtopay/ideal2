<?php declare(strict_types=1);

namespace POM\iDEAL;

use POM\iDEAL\Requests\Acquirer\AccessTokenRequest;
use POM\iDEAL\Requests\Hub\TransactionRequest;
use POM\iDEAL\Resources\AccessToken;

class iDEAL
{
    private AccessToken $accessToken;

    /**
     * @param string $merchantId
     * @param Bank $bank,
     * @param string $certificateFilePath
     * @param string $privateKeyFilePath
     * @param bool $sandbox
     */
    public function __construct(private readonly Config|INGConfig $config)
    {
    }

    public function createAccessTokenRequest(): AccessTokenRequest
    {
        return new AccessTokenRequest($this);
    }

    public function createTransactionRequest(AccessToken $accessToken, string $requestId): TransactionRequest
    {
        return new TransactionRequest(
            $this,
            $accessToken,
            $requestId
        );
    }

    /**
     * @return Config|INGConfig
     */
    public function getConfig(): Config|INGConfig
    {
        return $this->config;
    }
}
