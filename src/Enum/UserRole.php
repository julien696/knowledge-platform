<?php

namespace App\Enum;

enum UserRole: string
{
    case USER = 'user';
    case CLIENT = 'client';
    case ADMIN = 'admin';
}