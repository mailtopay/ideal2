<?php declare(strict_types=1);

namespace POM\iDEAL\Hub;

use Exception;
use Firebase\JWT\JWT;
use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Hub\Requests\AccessTokenRequest;
use POM\iDEAL\Hub\Requests\TransactionRequest;
use POM\iDEAL\Hub\Requests\TransactionStatusRequest;
use POM\iDEAL\Hub\Resources\AccessToken;
use stdClass;

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

    /**
     * Verify the callback Currence sends to a webhook
     *
     * @param string $callbackResponse raw json body
     * @param array|null $headers
     * @return bool
     */
    public function verifyCallbackResponse(string $callbackResponse, ?array $headers = null): bool
    {
        if (is_null($headers)) {
            $requestId  = $_SERVER['HTTP_REQUEST_ID'] ?? '';
            $sender     = $_SERVER['HTTP_X_SENDER'] ?? '';
            $signature  = $_SERVER['HTTP_SIGNATURE'] ?? '';
        } else {
            $requestId  = $headers['Request-Id'] ?? '';
            $sender     = $headers['X-Sender'] ?? '';
            $signature  = $headers['Signature'] ?? '';
        }

        if (empty($requestId) || empty($signature)) {
            return false;
        }

        if ($sender !== 'iDEAL') {
            return false;
        }

        $signatureWithPayload = str_replace('..', '.'.JWT::urlsafeB64Encode($callbackResponse).'.', $signature);

        $jwtHeaders = new stdClass();

        try {
            JWT::decode(
                $signatureWithPayload,
                $this->certificateStore->getCertificates(),
                $jwtHeaders,
            );
        } catch (Exception) {
            return false;
        }

        if ($requestId !== $jwtHeaders->{'https://idealapi.nl/jti'}) {
            return false;
        }

        return true;
    }
}
