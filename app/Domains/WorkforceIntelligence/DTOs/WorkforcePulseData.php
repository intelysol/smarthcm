<?php

namespace App\Domains\WorkforceIntelligence\DTOs;

class WorkforcePulseData
{
    public function __construct(
        public int $activeHeadcount,
        public float $scheduledFteToday,
        public float $actualHoursWorkedToday,
        public float $overtimeHoursToday,
        public float $absenceRateToday,
        public int $openCriticalAlerts,
        public string $dataFreshnessTimestamp,
        public array $hotspots = []
    ) {}

    public function toArray(): array
    {
        return [
            'active_headcount' => $this->activeHeadcount,
            'scheduled_fte_today' => $this->scheduledFteToday,
            'actual_hours_worked_today' => $this->actualHoursWorkedToday,
            'overtime_hours_today' => $this->overtimeHoursToday,
            'absence_rate_today' => $this->absenceRateToday,
            'open_critical_alerts' => $this->openCriticalAlerts,
            'data_freshness_timestamp' => $this->dataFreshnessTimestamp,
            'hotspots' => $this->hotspots,
        ];
    }
}
