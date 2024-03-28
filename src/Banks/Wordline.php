<?php

namespace POM\iDEAL\Banks;

final class Wordline extends BankBase
{
    public function __construct()
    {
        $this->setClient('RaboiDEAL');
        $this->setBaseUrl('https://digitalroutingservice.awltest.de');
    }
}