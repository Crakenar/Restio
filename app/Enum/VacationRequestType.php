<?php

namespace App\Enum;

enum VacationRequestType: string
{
    case VACATION = 'vacation';
    case SICK = 'sick';
    case PERSONAL = 'personal';
    case UNPAID = 'unpaid';
    case WFH = 'wfh';
}
