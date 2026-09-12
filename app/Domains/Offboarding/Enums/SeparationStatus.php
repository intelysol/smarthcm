<?php

namespace App\Domains\Offboarding\Enums;

enum SeparationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case NOTICE_PERIOD = 'notice_period';
    case OFFBOARDING = 'offboarding';
    case CLEARANCE = 'clearance';
    case FINAL_SETTLEMENT = 'final_settlement';
    case READY_FOR_EXIT = 'ready_for_exit';
    case EXITED = 'exited';
    case CANCELLED = 'cancelled';
    case REVERSED = 'reversed';
    case FAILED = 'failed';
}
