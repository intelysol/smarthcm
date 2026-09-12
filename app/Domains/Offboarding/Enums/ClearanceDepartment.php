<?php

namespace App\Domains\Offboarding\Enums;

enum ClearanceDepartment: string
{
    case HR = 'hr';
    case FINANCE = 'finance';
    case IT = 'it';
    case ASSETS = 'assets';
    case ADMIN = 'admin';
    case SECURITY = 'security';
}
