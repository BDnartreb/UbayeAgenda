<?php

namespace App\Enum;

enum FeeEnum: string
{
    case FREE = 'Entrée Libre';
    case FREEPRICE = 'Prix Libre';
    case PAID = 'Payant';
}