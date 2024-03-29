<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

final class Worldline extends BankBase implements BankInterface
{
    public function __construct()
    {
        $this->setClient('RaboiDEAL');
        $this->setApp('IDEAL');
        $this->setBaseUrl('https://digitalroutingservice.awltest.de');
    }
}