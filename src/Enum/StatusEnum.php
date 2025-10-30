<?php

namespace App\Enum;

enum StatusEnum: string
{
    case Association = 'Association';
    case Institution = 'Institution publique';
    case Individual = 'Particulier';
}