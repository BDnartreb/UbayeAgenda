<?php

namespace App\Enum;

enum EventStatusEnum: string
{
     case DRAFT = 'DRAFT';
     case ORGANISATIONS = 'ORGANISATIONS';
     case PUBLIC = 'PUBLIC';

     // case PRIVATE = 'Privé';
     // case COMMUNITY = 'Interne';
     // case PUBLIC = 'Public';
}