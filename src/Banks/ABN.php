<?php

namespace POM\iDEAL\Banks;

final class ABN extends BankBase
{
    public function __construct()
    {
        $this->setClient('ABN');
        $this->setApp('IDEAL');
        $this->setBaseUrl('https://ecommerce.abnamro.nl');
    }
}