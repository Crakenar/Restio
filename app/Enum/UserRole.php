<?php

namespace App\Enum;

enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EMPLOYEE = 'employee';

    public function isOwner(): bool
    {
        return $this === self::OWNER;
    }

    public function isOwnerOrAdmin(): bool
    {
        return $this === self::OWNER || $this === self::ADMIN;
    }

    public function canManageSubscription(): bool
    {
        return $this === self::OWNER;
    }
}
