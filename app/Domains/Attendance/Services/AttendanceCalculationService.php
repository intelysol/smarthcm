<?php

namespace App\Domains\Attendance\Services;

use Carbon\CarbonImmutable;

class AttendanceCalculationService
{
    /** @param list<array{timestamp:string,direction:string}> $events @return array<string,int|string|null> */
    public function calculate(array $events, string $scheduledStart, string $scheduledEnd, int $graceMinutes = 0): array
    {
        usort($events, fn (array $left, array $right) => strcmp($left['timestamp'], $right['timestamp']));
        $ins = array_values(array_filter($events, fn (array $event) => $event['direction'] === 'in'));
        $outs = array_values(array_filter($events, fn (array $event) => $event['direction'] === 'out'));
        if ($ins === [] || $outs === []) return ['status' => 'missing_punch', 'worked_minutes' => 0, 'late_minutes' => 0, 'early_departure_minutes' => 0, 'first_in' => $ins[0]['timestamp'] ?? null, 'last_out' => $outs === [] ? null : $outs[array_key_last($outs)]['timestamp']];
        $in = CarbonImmutable::parse($ins[0]['timestamp']); $out = CarbonImmutable::parse($outs[array_key_last($outs)]['timestamp']); $start = CarbonImmutable::parse($scheduledStart); $end = CarbonImmutable::parse($scheduledEnd); if ($end->lessThanOrEqualTo($start)) $end = $end->addDay(); if ($out->lessThan($in)) $out = $out->addDay();
        $late = max(0, $start->diffInMinutes($in, false) - $graceMinutes); $early = max(0, $out->diffInMinutes($end, false));
        return ['status' => $late > 0 ? 'late' : ($early > 0 ? 'early_departure' : 'present'), 'worked_minutes' => (int) $in->diffInMinutes($out), 'late_minutes' => (int) $late, 'early_departure_minutes' => (int) $early, 'first_in' => $in->toAtomString(), 'last_out' => $out->toAtomString()];
    }
}
