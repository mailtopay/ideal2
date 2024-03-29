<?php

namespace POM\iDEAL\Banks;

final class Worldline extends BankBase
{
    public function __construct()
    {
        $this->setClient('RaboiDEAL');
        $this->setApp('iDEAL');
        $this->setBaseUrl('https://digitalroutingservice.awltest.de');
    }
}