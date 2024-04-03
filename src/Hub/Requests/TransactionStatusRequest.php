<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Requests;

use POM\iDEAL\Hub\Resources\Transaction;

final class TransactionStatusRequest extends Request
{
    protected string $requestMethod = 'GET';

    public function execute(string $transactionId): Transaction
    {
        $this->endpoint = '/v2/merchant-cpsp/transactions/'.urlencode($transactionId);

        $data = parent::send();

        return Transaction::fromArray($data);
    }
}