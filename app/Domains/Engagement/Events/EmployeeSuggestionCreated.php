<?php

namespace App\Domains\Engagement\Events;

use App\Domains\Engagement\Models\EmployeeSuggestion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeSuggestionCreated
{
    use Dispatchable, SerializesModels;
    public function __construct(public EmployeeSuggestion $suggestion) {}
}
