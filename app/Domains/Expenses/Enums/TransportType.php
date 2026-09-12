<?php

namespace App\Domains\Expenses\Enums;

enum TransportType: string
{
    case FLIGHT = 'flight';
    case TRAIN = 'train';
    case BUS = 'bus';
    case CAR_RENTAL = 'car_rental';
    case TAXI = 'taxi';
    case PERSONAL_VEHICLE = 'personal_vehicle';
    case OTHER = 'other';
}
