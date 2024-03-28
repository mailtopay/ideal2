<?php declare(strict_types=1);

namespace POM\iDEAL\Wordline;

use POM\iDEAL\Wordline\Requests\AccessTokenRequest;
use POM\iDEAL\Wordline\Requests\TransactionRequest;
use POM\iDEAL\Wordline\Resources\AccessToken;
use POM\iDEAL\Worldline\Config;

class iDEAL
{
    private AccessToken $accessToken;

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