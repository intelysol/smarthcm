<?php
namespace App\Domains\Employee\Events;
use App\Domains\Employee\Models\Employee; use Illuminate\Foundation\Events\Dispatchable; use Illuminate\Queue\SerializesModels;
class EmployeeStatusChanged { use Dispatchable,SerializesModels; public function __construct(public readonly Employee $employee,public readonly string $from,public readonly string $to){} }
