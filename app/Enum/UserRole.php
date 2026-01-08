<?php

namespace App\Enum;

enum UserRole: string
{
    case ADMIN = 'admin';
    case EMPLOYEE = 'employee';
    case MANAGER = 'manager';
}
