<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

final class INGSandbox extends BankBase implements BankInterface
{
    public function __construct()
    {
        $this->setClient('unused');
        $this->setApp('unused');
        $this->setBaseUrl('https://api.sandbox.ideal-acquiring.ing.nl');
    }
}