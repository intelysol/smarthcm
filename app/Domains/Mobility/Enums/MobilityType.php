<?php

namespace App\Domains\Mobility\Enums;

enum MobilityType: string
{
    case INTERNATIONAL_ASSIGNMENT = 'international_assignment';
    case SHORT_TERM_ASSIGNMENT = 'short_term_assignment';
    case LONG_TERM_ASSIGNMENT = 'long_term_assignment';
    case PERMANENT_TRANSFER = 'permanent_transfer';
    case TEMPORARY_TRANSFER = 'temporary_transfer';
    case SECONDMENT = 'secondment';
    case DEPUTATION = 'deputation';
    case INTERNATIONAL_REMOTE_WORK = 'international_remote_work';
    case CROSS_BORDER_PROJECT = 'cross_border_project';
    case BUSINESS_TRAVELER = 'business_traveler';
    case RELOCATION = 'relocation';
    case EXPATRIATE_ASSIGNMENT = 'expatriate_assignment';
    case REPATRIATION = 'repatriation';
    case HOST_COUNTRY_ASSIGNMENT = 'host_country_assignment';
    case HOME_COUNTRY_ASSIGNMENT = 'home_country_assignment';
}
