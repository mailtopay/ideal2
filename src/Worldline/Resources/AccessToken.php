<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Resources;

use DateInterval;
readonly class AccessToken
{
    /**
     * @param string $token
     * @param DateInterval|null $expire
     */
    public function __construct(
        private string $token,
        private ?DateInterval $expire = null,
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
     * @return DateInterval|null
     */
    public function getExpire(): ?DateInterval
    {
        return $this->expire;
    }

}