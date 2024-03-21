<?php

namespace POM\iDeal\Resources;

use POM\iDeal\iDEAL;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Request;
use DateTime;
use DateInterval;

class HubToken
{
    private string  $algorithm;
    private string  $base64urlEncodedDigest;
    private string  $signingFile;
    private string  $signingPassphrase;
    private string  $tlsFile;
    private string  $tlsPassphrase;
    private string  $tlsKeyFile;
    private string  $tlsKeyPassphrase;

    private iDEAL  $iDEAL;

    /**
     * @param iDEAL $iDEAL
     * @param string $algorithm
     * @param string $base64urlEncodedDigest
     * @param string $signingFile
     * @param string $signingPassphrase
     * @param string $tlsFile
     * @param string $tlsPassphrase
     * @param string $tlsKeyFile
     * @param string $tlsKeyPassphrase
     */
    public function __construct(iDEAL $iDEAL, string $algorithm, string $base64urlEncodedDigest, string $signingFile, string $signingPassphrase, string $tlsFile, string $tlsPassphrase, string $tlsKeyFile, string $tlsKeyPassphrase)
    {
        $this->iDEAL = $iDEAL;
        $this->algorithm = $algorithm;
        $this->base64urlEncodedDigest = $base64urlEncodedDigest;
        $this->signingFile = $signingFile;
        $this->signingPassphrase = $signingPassphrase;
        $this->tlsFile = $tlsFile;
        $this->tlsPassphrase = $tlsPassphrase;
        $this->tlsKeyFile = $tlsKeyFile;
        $this->tlsKeyPassphrase = $tlsKeyPassphrase;
    }

    /**
     * @return string
     */
    private function createJWT()
    {
        $privateKey = openssl_pkey_get_private(
            file_get_contents($this->signingFile),
            $this->signingPassphrase
        );

        if ($privateKey === false) {
            throw new Exception('Could not load private key file');
        }

        $payload = [
            "iss" => $this->iDEAL->getMerchantId(),
            "sub" => $this->iDEAL->getMerchantId(),
            "aud" => $this->iDEAL->getBankBaseUrl(),
            "iat" => time(),
        ];

        $headers = [
            'alg' => $this->algorithm,
            'typ' => 'JWT',    // JWT type
            'x5t#S256' => $this->base64urlEncodedDigest,
        ];

        return JWT::encode($payload, $privateKey, $this->algorithm, null, $headers);
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doCall() {
        $client = new Client();
        $headers = [
            'Content-type' => 'application/x-www-form-urlencoded',
        ];
        $options = [
            'form_params' => [
                'grant_type' => 'client_credentials', // always client_credentials
                'client_id' => $this->iDEAL->getMerchantId(),
                'scope' => 'ideal2', // always ideal2
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer', // always the same
                'client_assertion' => $this->createJWT(),
            ],
            'cert'      => [$this->tlsFile, $this->tlsPassphrase],
            'ssl_key'   => [$this->tlsKeyFile, $this->tlsKeyPassphrase],
        ];

        $request  = new Request('POST', $this->iDEAL->getBankBaseUrl() . '/ideal2/merchanttoken', $headers);

        try {
            $response = $client->send($request, $options);

            if (!empty($response)) {
                $response = json_decode($response->getBody()->getContents());

                $expireDateTime = new DateTime();
                $expireDateTime->add(new DateInterval('PT'. $response->expires_in .'S'));

                $this->iDEAL->setHubToken($response->access_token);
                $this->iDEAL->setValidUntil($expireDateTime);
            }

        } catch(\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}