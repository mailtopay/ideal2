<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

final class ABN extends BankBase implements BankInterface
{
    public function __construct()
    {
        $this->setClient('ABN');
        $this->setApp('IDEAL');
        $this->setBaseUrl('https://ecommerce.abnamro.nl');
    }
}