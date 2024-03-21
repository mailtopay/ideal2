<?php

namespace POM\iDeal;

enum Bank: string
{
    case ING_TEST = 'https://api.sandbox.ideal-acquiring.ing.nl';
    case ING = 'https://api.ideal-acquiring.ing.nl';
}
