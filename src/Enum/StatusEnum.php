<?php

namespace App\Enum;

enum StatusEnum: string
{
    case ASSOCIATION = 'Association';
    case INSTITUTION = 'Institution publique';
    case INDIVIDUAL = 'Particulier';
}