<?php

namespace App\Enum;

enum StatusEnum: string
{
    case ASSOCIATION = 'Association/Collectif';
    case INSTITUTION = 'Collectivité';
    case PROFESSIONAL = 'Professionnel';
}