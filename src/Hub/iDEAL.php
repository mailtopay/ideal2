<?php declare(strict_types=1);

namespace POM\iDEAL\Hub;

use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Hub\Requests\AccessTokenRequest;
use POM\iDEAL\Hub\Requests\TransactionRequest;
use POM\iDEAL\Hub\Requests\TransactionStatusRequest;
use POM\iDEAL\Hub\Resources\AccessToken;

readonly final class iDEAL
{
    /**
     * @var HubCertificateStore
     */
    private HubCertificateStore $certificateStore;
    private AccessToken $accessToken;

    /**
     * @param Config $config
     * @throws IDEALException
     */
    public function __construct(private Config $config)
    {
        $this->certificateStore = new HubCertificateStore(
            $this->config->getCache(),
            $this->config->isTestMode(),
            $this->config->getCachePrefix(),
        );

        $this->accessToken = $this->retrieveAccessToken();
    }

    private function retrieveAccessToken(): AccessToken
    {
        $cacheKey = $this->config->getCachePrefix().'.accesstoken.'.$this->config->getMerchantId();

        $accessToken = $this->config->getCache()->get($cacheKey);

        // if the access token is still in cache, return that
        if (!is_null($accessToken)) {
            return new AccessToken(
                $accessToken['token'],
                $accessToken['id'],
            );
        }

        // else, get a new accesstoken from ING
        $accessTokenRequest = new AccessTokenRequest($this);

        $accessToken = $accessTokenRequest->execute();

        $this->getConfig()->getCache()->set(
            $cacheKey,
            [
                'token' => $accessToken->getToken(),
                'id'    => $accessToken->getId(),
            ],
            $accessToken->getExpire(),
        );

        return $accessToken;
    }

    /**
     * @return AccessTokenRequest
     */
    public function createAccessTokenRequest(): AccessTokenRequest
    {
        return new AccessTokenRequest($this);
    }

    /**
     * @param AccessToken $accessToken
     * @return TransactionRequest
     * @throws IDEALException
     */
    public function createTransactionRequest(): TransactionRequest
    {
        return new TransactionRequest(
            $this,
        );
    }

    /**
     * @param AccessToken $accessToken
     * @return TransactionStatusRequest
     * @throws IDEALException
     */
    public function createTransactionStatusRequest(): TransactionStatusRequest
    {
        return new TransactionStatusRequest(
            $this,
        );
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return HubCertificateStore
     */
    public function getCertificateStore(): HubCertificateStore
    {
        return $this->certificateStore;
    }

    public function getAccessToken(): AccessToken
    {
        return $this->accessToken;
    }
}
