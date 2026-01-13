<?php

namespace App\Enum;

enum SubscriptionInterval: string
{
    case MONTH = 'month';
    case YEAR = 'year';
    case ONE_TIME = 'one_time';
}
