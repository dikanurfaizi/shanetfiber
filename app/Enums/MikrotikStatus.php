<?php

namespace App\Enums;

enum MikrotikStatus: string
{
    case PPPOE_DISABLE = 'true';
    case PPPOE_ENABLE = 'false';
}
