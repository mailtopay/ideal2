<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

final class BNG extends BankBase implements BankInterface
{
    public function __construct()
    {
        $this->setClient('BngNL');
        $this->setApp('IDEAL');
        $this->setBaseUrl('https://openbanking1.worldline-solutions.com');
    }
}