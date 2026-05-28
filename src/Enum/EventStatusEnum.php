<?php

namespace App\Enum;

enum EventStatusEnum: string
{
     case PRIVATE = 'Privé';
     case COMMUNITY = 'Interne';
     case PUBLIC = 'Public';
}