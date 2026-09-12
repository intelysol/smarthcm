<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Services;

use App\Domains\HealthSafety\Models\HcmEmployeeHealthRequirement;
use App\Domains\HealthSafety\Models\HcmMedicalFitnessRecord;
use App\Domains\HealthSafety\Models\HcmMedicalRestriction;
use App\Domains\HealthSafety\Models\HcmReturnToWorkCase;
use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Models\HcmSafetyIncidentAction;
use App\Domains\HealthSafety\Models\HcmWorkplaceAccommodation;
use App\Domains\HealthSafety\Models\HcmWorkplaceExposure;
use Illuminate\Support\Carbon;

class HealthReportingService
{
    /**
     * Compute HSE safety KPIs (LTIR, TRIR, Severity Rate, Days Away).
     * Standard OSHA formulas:
     * TRIR = (Recordable Incidents * 200,000) / Total Hours Worked
     * LTIR = (Lost Time Incidents * 200,000) / Total Hours Worked
     * Severity Rate = (Lost Work Days * 200,000) / Total Hours Worked
     */
    public function calculateSafetyKpis(
        string $tenantId,
        ?string $companyId = null,
        ?string $year = null,
        int $totalHoursWorked = 1000000
    ): array {
        $year = $year ?? Carbon::now()->format('Y');

        $query = HcmSafetyIncident::where('tenant_id', $tenantId)
            ->whereYear('incident_datetime', $year);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $totalIncidents = (clone $query)->count();
        $recordableIncidents = (clone $query)->where('is_osha_reportable', true)->count();
        $lostTimeIncidents = (clone $query)->where('lost_time_injury', true)->count();
        $totalLostDays = (clone $query)->sum('lost_work_days');

        // Prevent division by zero
        $hours = max(1, $totalHoursWorked);

        $trir = round(($recordableIncidents * 200000) / $hours, 2);
        $ltir = round(($lostTimeIncidents * 200000) / $hours, 2);
        $severityRate = round(($totalLostDays * 200000) / $hours, 2);

        return [
            'year' => (int) $year,
            'total_incidents' => $totalIncidents,
            'recordable_incidents' => $recordableIncidents,
            'lost_time_incidents' => $lostTimeIncidents,
            'total_lost_work_days' => (int) $totalLostDays,
            'hours_worked_basis' => $hours,
            'trir' => $trir,
            'ltir' => $ltir,
            'severity_rate' => $severityRate,
        ];
    }

    /**
     * Compile OSHA Form 300 / 300A Log summary for regulatory compliance.
     */
    public function generateOsha300Log(string $tenantId, string $year, ?string $companyId = null): array
    {
        $query = HcmSafetyIncident::where('tenant_id', $tenantId)
            ->where('is_osha_reportable', true)
            ->whereYear('incident_datetime', $year)
            ->with(['employee', 'workLocation', 'department']);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $incidents = $query->orderBy('incident_datetime', 'asc')->get();

        $rows = $incidents->map(function (HcmSafetyIncident $inc) {
            return [
                'case_no' => $inc->incident_number,
                'employee_name' => $inc->employee ? ($inc->employee->first_name . ' ' . $inc->employee->last_name) : 'Non-employee / Unknown',
                'job_title' => $inc->employee?->designation?->name ?? 'N/A',
                'date_of_injury' => $inc->incident_datetime?->format('Y-m-d'),
                'where_event_occurred' => ($inc->workLocation?->name ?? 'Main Site') . ' - ' . ($inc->location_description ?? 'N/A'),
                'description' => $inc->description,
                'is_death' => $inc->severity === 'fatal',
                'days_away_from_work' => $inc->lost_work_days,
                'is_other_recordable' => !$inc->lost_time_injury && $inc->severity !== 'fatal',
            ];
        });

        return [
            'calendar_year' => $year,
            'establishment_recordable_count' => $incidents->count(),
            'total_deaths' => $incidents->where('severity', 'fatal')->count(),
            'total_lost_time_cases' => $incidents->where('lost_time_injury', true)->count(),
            'total_other_recordable_cases' => $incidents->where('lost_time_injury', false)->where('severity', '!=', 'fatal')->count(),
            'total_days_away' => (int) $incidents->sum('lost_work_days'),
            'records' => $rows,
        ];
    }


    /**
     * Get aggregate overview metrics for executive Health & Safety dashboard.
     */
    public function getDashboardMetrics(string $tenantId, ?string $companyId = null): array
    {
        $now = Carbon::now();

        $incidentsQuery = HcmSafetyIncident::where('tenant_id', $tenantId);
        $requirementsQuery = HcmEmployeeHealthRequirement::where('tenant_id', $tenantId);
        $fitnessQuery = HcmMedicalFitnessRecord::where('tenant_id', $tenantId);
        $restrictionsQuery = HcmMedicalRestriction::where('tenant_id', $tenantId)->where('is_active', true);
        $rtwQuery = HcmReturnToWorkCase::where('tenant_id', $tenantId);
        $actionsQuery = HcmSafetyIncidentAction::where('tenant_id', $tenantId);
        $exposuresQuery = HcmWorkplaceExposure::where('tenant_id', $tenantId);

        if ($companyId) {
            $incidentsQuery->where('company_id', $companyId);
            $requirementsQuery->where('company_id', $companyId);
            $fitnessQuery->where('company_id', $companyId);
            $restrictionsQuery->where('company_id', $companyId);
            $rtwQuery->where('company_id', $companyId);
            $exposuresQuery->where('company_id', $companyId);
        }

        // Days since last lost-time incident
        $lastLostTimeIncident = (clone $incidentsQuery)
            ->where('is_lost_time', true)
            ->orderBy('incident_date', 'desc')
            ->first();

        $daysWithoutLostTime = $lastLostTimeIncident && $lastLostTimeIncident->incident_date
            ? (int) Carbon::parse($lastLostTimeIncident->incident_date)->diffInDays($now)
            : 365;

        return [
            'days_without_lost_time' => $daysWithoutLostTime,
            'open_incidents_count' => (clone $incidentsQuery)->whereNotIn('status', ['closed', 'cancelled'])->count(),
            'active_restrictions_count' => $restrictionsQuery->count(),
            'active_rtw_cases_count' => (clone $rtwQuery)->whereNotIn('status', ['completed', 'failed_accommodated'])->count(),
            'overdue_actions_count' => (clone $actionsQuery)->whereIn('status', ['open', 'in_progress'])->whereDate('due_date', '<', Carbon::today())->count(),
            'expiring_fitness_30d_count' => (clone $fitnessQuery)->where('status', 'fit')->whereBetween('valid_until', [Carbon::today(), Carbon::today()->addDays(30)])->count(),
            'unfit_employees_count' => (clone $fitnessQuery)->where('status', 'unfit')->count(),
            'exposures_exceeded_count' => (clone $exposuresQuery)->where('threshold_exceeded', true)->count(),
            'safety_kpis_ytd' => $this->calculateSafetyKpis($tenantId, $companyId, $now->format('Y')),
        ];
    }
}
