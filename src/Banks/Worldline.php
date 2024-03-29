<?php

namespace POM\iDEAL\Banks;

final class Worldline extends BankBase
{
    public function __construct()
    {
        $this->setClient('RaboiDEAL');
        $this->setApp('IDEAL');
        $this->setBaseUrl('https://digitalroutingservice.awltest.de');
    }
}