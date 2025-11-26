<?php

namespace App\Enum;

enum FeeEnum: string
{
    case FREE = 'Gratuit';
    case FREEPRICE = 'Prix Libre';
    case PAID = 'Payant';
}