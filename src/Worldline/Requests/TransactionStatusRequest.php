<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Requests;

use POM\iDEAL\Worldline\Resources\TransactionStatus;

final class TransactionStatusRequest extends Request
{
    protected string $requestMethod = 'GET';
    /**
     * @param string $transactionId
     * @return TransactionStatus
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(string $transactionId): TransactionStatus
    {
        $this->endpoint = '/xs2a/routingservice/services/ob/pis/v3/payments/'. urlencode($transactionId) .'/status';

        $data = parent::send();

        return TransactionStatus::fromArray($data);
    }
}