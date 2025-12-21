<?php

namespace App\Enums;

enum OrderStatus: string
{
    case UNPAID = 1;
    case PAID = 2;
    case EXPIRED = 3;
    case CANCELED = 4;
}
