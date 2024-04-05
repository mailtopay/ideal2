<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Requests;

use DateInterval;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Worldline\iDEAL;
use POM\iDEAL\Worldline\Resources\AccessSignature;
use POM\iDEAL\Worldline\Resources\AccessToken;

readonly class AccessTokenRequest
{
    /**
     * @param iDEAL $iDEAL
     */
    public function __construct(private iDEAL $iDEAL)
    {
    }

    /**
     * @throws IDEALException
     */
    public function execute(): AccessToken
    {
        $client = new Client();

        $dateTime = new DateTime();
        $accessSignature = new AccessSignature($this->iDEAL, $dateTime);

        $headers = [
            'App' => $this->iDEAL->getConfig()->getBank()->getApp(),
            'Client' => $this->iDEAL->getConfig()->getBank()->getClient(),
            'Id' => $this->iDEAL->getConfig()->getMerchantId(),
            'Date' => $dateTime->format(DATE_ATOM),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'Authorization' => $accessSignature->getSignature(),
        ];

        $options = [
            'form_params' => [
                'grant_type' => 'client_credentials'
        ]];

        $request  = new Request('POST', $this->iDEAL->getConfig()->getBaseUrl() . '/xs2a/routingservice/services/authorize/token', $headers);

        try {
            $response = $client->send($request, $options);
        } catch (GuzzleException $e) {
            throw new IDEALException('Access token request failed: ' . $e->getMessage());
        }

        $responseBody = $response->getBody()->getContents();
        $response = json_decode($responseBody);

        $expireDateTime = new DateTime();
        try {
            $expireDateTime->add(new DateInterval('PT' . $response->expires_in . 'S'));
        } catch (\Exception $e) {
            throw new IDEALException('DateTime interval failed: ' . $e->getMessage());
        }

        return new AccessToken($response->access_token, $expireDateTime);
    }

}