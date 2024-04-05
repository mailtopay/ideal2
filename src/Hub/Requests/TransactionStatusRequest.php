<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Requests;

use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Hub\Resources\Transaction;

final class TransactionStatusRequest extends Request
{
    protected string $requestMethod = 'GET';

    /**
     * @param string $transactionId
     * @return Transaction
     * @throws IDEALException
     */
    public function execute(string $transactionId): Transaction
    {
        $this->endpoint = '/v2/merchant-cpsp/transactions/'.urlencode($transactionId);

        $data = parent::send();

        return Transaction::fromArray($data);
    }
}