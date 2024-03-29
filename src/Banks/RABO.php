<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

final class RABO extends BankBase implements BankInterface
{
    public function __construct()
    {
        $this->setClient('RaboiDEAL');
        $this->setApp('IDEAL');
        $this->setBaseUrl('https://ideal.rabobank.nl');
    }
}