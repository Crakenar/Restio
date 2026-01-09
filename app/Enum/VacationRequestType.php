<?php

namespace App\Enum;

enum VacationRequestType: string
{
    case UNKNOWN = 'UNKNOWN';
    case PAID = 'PAID';
    case SICK = 'SICK';
    case WORK_FROM_HOME = 'WORK_FROM_HOME';
    case VACATION = 'VACATION';
}
