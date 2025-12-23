<?php

namespace App\Enums;

enum SubscriptionStatusEnum: string
{
    case TRIAL = 'trial';
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case CANCELED = 'canceled';
    case EXPIRED = 'expired';
}

