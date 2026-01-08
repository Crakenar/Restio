<?php

namespace App\Enum;

enum VacationRequestType: string
{
    case UNKNOWN = 'UNKNOWN';
    case PAID = 'PAID';
    case PENDING = 'PENDING';
    case REJECTED = 'REJECTED';
}
