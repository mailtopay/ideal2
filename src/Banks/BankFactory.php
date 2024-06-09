<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

class BankFactory
{
    /** Creates a Bank class based on bank codes
     *
     * @param string $bankcode
     * @return BankInterface
     */
    public static function bankFromString(string $bankcode): BankInterface
    {
        return match ($bankcode){
            'abn' => new ABN(),
            'bng' => new BNG(),
            'bnp' => new BNP(),
            'db' => new DB(),
            'ing' => new ING(),
            'ingsandbox' => new INGSandbox(),
            'rabo' => new RABO(),
            'worldline' => new Worldline(),
        };
    }
}