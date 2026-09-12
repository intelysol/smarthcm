<?php

namespace App\Domains\WorkforceProductivity\Enums;

enum ProductiveTimeCategory: string
{
    case PRODUCTIVE = 'PRODUCTIVE';
    case NON_PRODUCTIVE = 'NON_PRODUCTIVE';
    case TRAINING = 'TRAINING';
    case MEETING = 'MEETING';
    case ADMINISTRATION = 'ADMINISTRATION';
    case WAITING = 'WAITING';
    case IDLE = 'IDLE';
    case BREAK = 'BREAK';
    case ABSENCE = 'ABSENCE';
    case OVERTIME_PRODUCTIVE = 'OVERTIME_PRODUCTIVE';
    case OVERTIME_NON_PRODUCTIVE = 'OVERTIME_NON_PRODUCTIVE';
}
