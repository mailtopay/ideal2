<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

final class DB extends BankBase implements BankInterface
{
    public function __construct()
    {
        $this->setClient('DeubaNL');
        $this->setApp('IDEAL');
        $this->setBaseUrl('https://myideal.db.com');
    }
}