<?php declare(strict_types=1);

namespace POM\iDEAL;

enum BankUrl: string
{
    case ING = 'https://api.ideal-acquiring.ing.nl';
    case RABO = 'https://ideal.rabobank.nl';
    case ABN = 'https://ecommerce.abnamro.nl';
    case BNG = 'https://openbanking1.worldline-solutions.com/';
    case BNP = 'https://unknown.bnp';
    case DB = 'https://unknown.db';
    case WORDLINE_TEST = 'https://digitalroutingservice.awltest.de';
    case ING_TEST = 'https://api.sandbox.ideal-acquiring.ing.nl';
}
