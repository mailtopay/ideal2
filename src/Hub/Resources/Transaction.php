<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Resources;

use DateTime;
use POM\iDEAL\Hub\TransactionStatus;

readonly class Transaction
{
    public function __construct(
        private string $transactionId,
        private DateTime $created,
        private DateTime $expire,
        private string $description,
        private string $reference,
        private ?string $transactionType,
        private ?string $transactionFlow,
        private int $amount,
        private ?string $redirectUrl,
        private TransactionStatus $status,
        private ?string $iban,
        private ?string $bic,
        private ?string $accountOwner,
    ) {
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

    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function getTransactionFlow(): ?string
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

    /**
     * @return ?string
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @return ?string
     */
    public function getBic(): ?string
    {
        return $this->bic;
    }

    /**
     * @return ?string
     */
    public function getAccountOwner(): ?string
    {
        return $this->accountOwner;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['transactionId'],
            new DateTime($data['createdDateTimestamp']),
            new DateTime($data['expiryDateTimestamp']),
            $data['description'],
            $data['reference'],
            $data['transactionType'] ?? null,
            $data['transactionFlow'] ?? null,
            $data['amount']['amount'],
            $data['links']['redirectUrl']['href'] ?? null,
            TransactionStatus::from($data['status'] ?? 'OPEN'),
            isset($data['debtor']) ? $data['debtor']['iban'] ?? null : null,
            isset($data['debtor']) ? $data['debtor']['bic'] ?? null : null,
            isset($data['debtor']) ? $data['debtor']['name'] ?? null : null,
        );
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }
}