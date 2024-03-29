<?php

namespace POM\iDEAL\Banks;

final class BNP extends BankBase
{
    public function __construct()
    {
        $this->setClient('');
        $this->setApp('IDEAL');
        $this->setBaseUrl('');
    }
}