<?php

namespace App\Enums;

enum DealStatusEnum: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case WON = 'won';
    case LOST = 'lost';
}




