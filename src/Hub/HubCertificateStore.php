<?php declare(strict_types=1);

namespace POM\iDEAL\Hub;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use POM\iDEAL\Exceptions\IDEALException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

readonly class HubCertificateStore
{
    /**
     * @throws IDEALException
     */
    public function __construct(private CacheInterface $cache, private bool $testMode, private string $cachePrefix)
    {
        $this->retrieveCertificates();
    }

    /**
     * Get certificate list from cache
     *
     * @return array<string,Key>
     * @throws IDEALException
     */
    public function getCertificates(): array
    {
        // cache could have expired maybe
        try {
            if (!$this->cache->has($this->cachePrefix . '.certificates')) {
                $this->retrieveCertificates();
            }
        } catch (InvalidArgumentException) {
            throw new IDEALException('Failed interfacing with caching layer, this shouldnt happen');
        }

        $certificates = [];

        try {
            $cachedCertificates = $this->cache->get($this->cachePrefix . '.certificates');
        } catch (InvalidArgumentException) {
            throw new IDEALException('Failed interfacing with caching layer, this shouldnt happen');
        }

        foreach ($cachedCertificates as $cachedCertificate) {
            $certificates[$cachedCertificate['kid']] = new Key($cachedCertificate['certificate'], $cachedCertificate['algorithm']);
        }

        return $certificates;
    }

    /**
     * @throws IDEALException
     */
    private function retrieveCertificates(): void
    {
        // the certificates are still in cache so we dont need to retrieve them
        try {
            if ($this->cache->has($this->cachePrefix . '.certificates')) {
                return;
            }
        } catch (InvalidArgumentException) {
            throw new IDEALException('Failed interfacing with caching layer, this shouldnt happen');
        }

        // else we need to retrieve new certificates from ideal hub
        $client = new Client([
            'base_uri' => $this->testMode ? 'https://ext.idealapi.nl' : 'https://idealapi.nl',
        ]);

        $request = new Request(
            'GET',
            '/acquirer-certificates'
        );

        try {
            $response = $client->send($request);
        } catch (GuzzleException $e) {
            throw new IDEALException("Hub request failed: {$e->getMessage()}");
        }

        $certificates = json_decode($response->getBody()->getContents(), true)['keys'];

        $certificatesToCache = [];

        foreach ($certificates as $certificate) {
            if (!$this->verifyCertificate($certificate)) {
                continue;
            }

            $certificatesToCache[] = [
                'kid' => $certificate['kid'],
                'certificate' => "-----BEGIN CERTIFICATE-----\n{$certificate['x5c'][0]}\n-----END CERTIFICATE-----",
                'algorithm' => $certificate['alg'],
            ];
        }

        try {
            $this->cache->set($this->cachePrefix . '.certificates', $certificatesToCache, new DateInterval('PT1H'));
        } catch (InvalidArgumentException) {
            throw new IDEALException('Failed interfacing with caching layer, this shouldnt happen');
        }
    }

    /**
     * @param array $certificateData Certificate data from hub response
     * @return bool
     */
    private function verifyCertificate(array $certificateData): bool
    {
        $x509Certificate = "-----BEGIN CERTIFICATE-----\n{$certificateData['x5c'][0]}\n-----END CERTIFICATE-----";

        // get x509 attributes
        $certInfo = openssl_x509_parse($x509Certificate);

        if (!str_contains($certInfo['extensions']['certificatePolicies'], 'Policy: 2.23.140.1.2.2')) {
            return false;
        }

        // check certificate expiry date
        $validFrom = DateTimeImmutable::createFromFormat('ymdHis*', $certInfo['validFrom'], new DateTimeZone('UTC'));
        $validTo = DateTimeImmutable::createFromFormat('ymdHis*', $certInfo['validTo'], new DateTimeZone('UTC'));
        $now = new DateTimeImmutable();

        if ($validFrom > $now) {
            return false;
        }

        if ($validTo < $now) {
            return false;
        }

        if ($certInfo['subject']['C'] !== 'LU') {
            return false;
        }

        // organisation name should be Payconiq International S.A.
        if ($certInfo['subject']['O'] !== 'Payconiq International S.A.') {
            return false;
        }

        // country name should be Luxembourg
        if ($certInfo['subject']['L'] !== 'Luxembourg' && $certInfo['subject']['ST'] !== 'Luxembourg' && $certInfo['subject']['S'] !== 'Luxembourg') {
            return false;
        }

        // check if certificate is signed with intermediate public key
        $intermediateCertificate = "-----BEGIN CERTIFICATE-----\n{$certificateData['x5c'][1]}\n-----END CERTIFICATE-----";

        if (openssl_x509_verify($x509Certificate, $intermediateCertificate) !== 1) {
            return false;
        }

        // check if intermediate certificate is signed with root public key
        $rootCertificate = "-----BEGIN CERTIFICATE-----\n{$certificateData['x5c'][2]}\n-----END CERTIFICATE-----";

        if (openssl_x509_verify($intermediateCertificate, $rootCertificate) !== 1) {
            return false;
        }

        // check if intermediate certificate is known in trusted CA list
        if (openssl_x509_checkpurpose($intermediateCertificate, 0) !== true) {
            return false;
        }

        return true;
    }

}