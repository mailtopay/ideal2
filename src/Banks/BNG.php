<?php

namespace POM\iDEAL\Banks;

final class BNG extends BankBase
{
    public function __construct()
    {
        $this->setClient('BngNL');
        $this->setBaseUrl('https://openbanking1.worldline-solutions.com');
    }
}