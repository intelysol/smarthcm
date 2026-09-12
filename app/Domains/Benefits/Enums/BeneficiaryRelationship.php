<?php

namespace App\Domains\Benefits\Enums;

enum BeneficiaryRelationship: string
{
    case SPOUSE = 'spouse';
    case CHILD = 'child';
    case PARENT = 'parent';
    case SIBLING = 'sibling';
    case LEGAL_DEPENDENT = 'legal_dependent';
    case ESTATE = 'estate';
    case OTHER = 'other';
}
