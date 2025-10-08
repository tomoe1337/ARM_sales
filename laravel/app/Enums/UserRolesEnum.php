<?php
namespace App\Enums;

enum UserRolesEnum: string
{
    case HEAD = 'head';
    case MANAGER = 'manager';
    case ADMIN = 'admin';
}
