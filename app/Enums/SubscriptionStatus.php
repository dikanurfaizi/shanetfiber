<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case CANCELLED = 'cancelled';
    case TERMINATED = 'terminated'; // putus permanen
}
