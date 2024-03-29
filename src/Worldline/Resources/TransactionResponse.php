<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Resources;

use DateTime;

readonly class TransactionResponse
{
    public function __construct(
        private string $transactionId,
        private DateTime $expire,
        private string $redirectUrl,
    )
    {
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
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
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['CommonPaymentData']['PaymentId'],
            new DateTime($data['CommonPaymentData']['ExpiryDateTimestamp']),
            $data['Links']['RedirectUrl']['Href'],
        );
    }
}