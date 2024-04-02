<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

final class BNP extends BankBase implements BankInterface
{
    public function __construct()
    {
        $this->setClient('BnppNL');
        $this->setApp('IDEAL');
        $this->setBaseUrl('https://ecommerce.bnpparibas.com');
    }
}