<?php

namespace App\Domains\Engagement\Enums;

enum RecognitionType: string
{
    case Peer = 'peer';
    case Manager = 'manager';
    case Achievement = 'achievement';
    case Teamwork = 'teamwork';
    case Innovation = 'innovation';
    case CustomerService = 'customer_service';
    case Leadership = 'leadership';
    case Values = 'values';
}
