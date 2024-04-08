<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Resources;

use DateInterval;

readonly class AccessToken
{
    public function __construct(
        private string $token,
        private string $id,
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
     * @return ?DateInterval
     */
    public function getExpire(): ?DateInterval
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