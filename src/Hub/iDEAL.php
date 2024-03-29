<?php declare(strict_types=1);

namespace POM\iDEAL\Hub;

use POM\iDEAL\Hub\Requests\AccessTokenRequest;
use POM\iDEAL\Hub\Requests\TransactionRequest;
use POM\iDEAL\Hub\Resources\AccessToken;

class iDEAL
{

    /**
     * @param Config $config
     */
    public function __construct(private readonly Config $config)
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
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
