<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Resources;

use DateTime;

readonly class TransactionResponse
{
    public function __construct(
        private string $transactionId,
        private DateTime $created,
        private DateTime $expire,
        private string $description,
        private string $reference,
        private string $transactionType,
        private string $transactionFlow,
        private int $amount,
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
    public function getCreated(): DateTime
    {
        return $this->created;
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    /**
     * @return string
     */
    public function getTransactionFlow(): string
    {
        return $this->transactionFlow;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
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
            $data['transactionId'],
            new DateTime($data['createdDateTimestamp']),
            new DateTime($data['expiryDateTimestamp']),
            $data['description'],
            $data['reference'],
            $data['transactionType'],
            $data['transactionFlow'],
            $data['amount']['amount'],
            $data['links']['redirectUrl']['href'],
        );
    }
}