<?php

namespace App\Domains\Expenses\Enums;

enum BookingType: string
{
    case FLIGHT = 'flight';
    case HOTEL = 'hotel';
    case TRAIN = 'train';
    case CAR_RENTAL = 'car_rental';
    case BUS = 'bus';
    case OTHER = 'other';
}
