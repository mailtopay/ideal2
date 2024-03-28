<?php declare(strict_types=1);

namespace POM\iDEAL;

enum Bank: string
{
    case ING_TEST = 'https://api.sandbox.ideal-acquiring.ing.nl';
    case ING = 'https://api.ideal-acquiring.ing.nl';
}
