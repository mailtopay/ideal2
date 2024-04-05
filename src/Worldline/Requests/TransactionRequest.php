<?php declare(strict_types=1);

namespace POM\iDEAL\Worldline\Requests;

use POM\iDEAL\Exceptions\IDEALException;
use POM\iDEAL\Worldline\Resources\Transaction;

class TransactionRequest extends Request
{
    protected string $requestMethod = 'POST';
    protected string $endpoint = '/xs2a/routingservice/services/ob/pis/v3/payments';

    /**
     * @param int $amount
     * @param string $reference
     * @param string $returnUrl
     * @return Transaction
     * @throws IDEALException
     */
    public function execute(int $amount, string $reference, string $returnUrl): Transaction
    {
        $amount = number_format($amount / 100, 2, '.', '');

        $this->body = [
            'PaymentProduct'    => [
                'IDEAL',
            ],
            'CommonPaymentData' => [
                'Amount' => [
                    'Type' => 'Fixed',
                    'Amount' => $amount,
                    'Currency' => 'EUR'
                ],
                'RemittanceInformation' => 'iDEAL | POM',
                'RemittanceInformationStructured' => [
                    'Reference' => $reference
                ]
            ],
            'IDEALPayments'     => [
                'UseDebtorToken' => false,
                'FlowType'       => 'Standard',
            ],
        ];

        $this->setHeader('InitiatingPartyNotificationUrl', $this->iDEAL->getConfig()->getNotificationUrl());
        $this->setHeader('InitiatingPartyReturnUrl', $returnUrl);

        $result = parent::send();

        return Transaction::fromArray($result);
    }
}