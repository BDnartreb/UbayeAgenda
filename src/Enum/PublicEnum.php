<?php

namespace App\Enum;

enum PublicEnum: string
{
    case All = 'Tout public';
    case Child = 'Enfant';
    case Teen = 'Adolescent';
    case Adult = 'Adulte';
}