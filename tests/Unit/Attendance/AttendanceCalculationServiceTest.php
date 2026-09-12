<?php

namespace Tests\Unit\Attendance;

use App\Domains\Attendance\Services\AttendanceCalculationService;
use PHPUnit\Framework\TestCase;

class AttendanceCalculationServiceTest extends TestCase
{
    public function test_overnight_shift_is_calculated_against_its_shift_window(): void
    {
        $result = (new AttendanceCalculationService)->calculate([['timestamp' => '2026-08-20 21:55:00', 'direction' => 'in'], ['timestamp' => '2026-08-21 06:08:00', 'direction' => 'out']], '2026-08-20 22:00:00', '2026-08-20 06:00:00', 5);
        $this->assertSame('present', $result['status']); $this->assertSame(493, $result['worked_minutes']);
    }
}
