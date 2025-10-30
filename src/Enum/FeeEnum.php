<?php

namespace App\Enum;

enum FeeEnum: string
{
    case FREE = 'Entrée Libre';
    case FREEPAID = 'Prix Libre';
    case PAID = 'Payant';
}