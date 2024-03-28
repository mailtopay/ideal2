<?php

namespace POM\iDEAL\Banks;

final class RABO extends BankBase
{
    public function __construct()
    {
        $this->setClient('RaboiDEAL');
        $this->setBaseUrl('https://ideal.rabobank.nl');
    }
}