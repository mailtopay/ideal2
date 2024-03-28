<?php declare(strict_types=1);

namespace POM\iDEAL\Resources;

use POM\iDEAL\iDEAL;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Request;
use DateTime;
use DateInterval;

/**
 *
 */
readonly class AccessToken
{
    public function __construct(
        private string $token,
        private DateTime $expire,
        private string $id
    ) {
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return DateTime
     */
    public function getExpire(): DateTime
    {
        return $this->expire;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

}