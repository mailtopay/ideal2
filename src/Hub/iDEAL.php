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

    /**
     * @param Config $config
     * @throws IDEALException
     */
    public function __construct(private Config $config)
    {
        $this->certificateStore = new HubCertificateStore($this->config->getCache(), $this->config->isTestMode());
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
    public function createTransactionRequest(AccessToken $accessToken): TransactionRequest
    {
        return new TransactionRequest(
            $this,
            $accessToken,
        );
    }

    /**
     * @param AccessToken $accessToken
     * @return TransactionStatusRequest
     * @throws IDEALException
     */
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

    /**
     * @return HubCertificateStore
     */
    public function getCertificateStore(): HubCertificateStore
    {
        return $this->certificateStore;
    }
}
