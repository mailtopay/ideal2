<?php

namespace POM\iDEAL\Banks;

final class ABN extends BankBase
{
    public function __construct()
    {
        $this->setClient('ABN');
        $this->setBaseUrl('https://ecommerce.abnamro.nl');
    }
}