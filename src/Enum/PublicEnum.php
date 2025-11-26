<?php

namespace App\Enum;

enum PublicEnum: string
{
    case ALL = 'Tout public';
    case CHILD = 'Enfant';
    case TEEN = 'Jeune';
    case ADULT = 'Adulte';
}