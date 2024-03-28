<?php

namespace POM\iDEAL\Banks;

final class DB extends BankBase
{
    public function __construct()
    {
        $this->setClient('');
        $this->setBaseUrl('');
    }
}