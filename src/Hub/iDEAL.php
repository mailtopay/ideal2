<?php declare(strict_types=1);

namespace POM\iDEAL\Hub;

use POM\iDEAL\Hub\Requests\AccessTokenRequest;
use POM\iDEAL\Hub\Requests\TransactionRequest;
use POM\iDEAL\Hub\Requests\TransactionStatusRequest;
use POM\iDEAL\Hub\Resources\AccessToken;

readonly final class iDEAL
{

    /**
     * @param Config $config
     */
    public function __construct(private Config $config)
    {
    }

    public function createAccessTokenRequest(): AccessTokenRequest
    {
        return new AccessTokenRequest($this);
    }

    public function createTransactionRequest(AccessToken $accessToken): TransactionRequest
    {
        return new TransactionRequest(
            $this,
            $accessToken,
        );
    }

    public function createTransactionStatusRequest(AccessToken $accessToken): TransactionStatusRequest
    {
        return new TransactionStatusRequest(
            $this,
            $accessToken,
        );
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
