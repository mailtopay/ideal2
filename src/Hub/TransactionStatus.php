<?php

namespace POM\iDEAL\Hub;

enum TransactionStatus: string
{
    case OPEN = 'OPEN';
    case IDENTIFIED = 'IDENTIFIED';
    case EXPIRED = 'EXPIRED';
    case CANCELLED = 'CANCELLED';
    case SUCCESS = 'SUCCESS';
    case FAILURE = 'FAILURE';
}
