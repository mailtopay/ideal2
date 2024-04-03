<?php declare(strict_types=1);

namespace POM\iDEAL\Hub\Requests;

use POM\iDEAL\Hub\Resources\Transaction;

final class TransactionRequest extends Request
{
    protected string $endpoint = '/v2/merchant-cpsp/transactions';
    protected string $requestMethod = 'POST';

    public function execute(int $amount, string $description, string $reference, string $returnUrl): Transaction
    {
        $this->body = [
            'amount' => [
                'amount' => $amount,
                'type'  => 'FIXED',
                'currency' => 'EUR'
            ],
            'creditor' => [
                "countryCode" => "NL",
            ],
            'returnUrl' => $returnUrl,
            'description' => $description,
            'reference' => $reference,
        ];
        
        $transactionData = $this->send();

        return Transaction::fromArray($transactionData);
    }
}