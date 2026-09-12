<?php

declare(strict_types=1);

namespace App\Domains\HealthSafety\Services;

use App\Domains\HealthSafety\Events\SafetyIncidentClosed;
use App\Domains\HealthSafety\Events\SafetyIncidentReported;
use App\Domains\HealthSafety\Events\SafetyIncidentSeverityEscalated;
use App\Domains\HealthSafety\Models\HcmSafetyIncident;
use App\Domains\HealthSafety\Models\HcmSafetyIncidentWitness;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SafetyIncidentService
{
    /**
     * Report an occupational safety incident, near miss, or hazard.
     */
    public function reportIncident(array $data, ?int $userId = null): HcmSafetyIncident
    {
        return DB::transaction(function () use ($data, $userId) {
            $incidentNumber = $data['incident_number'] ?? $this->generateIncidentNumber($data['tenant_id']);

            $dateTime = isset($data['incident_datetime'])
                ? Carbon::parse($data['incident_datetime'])
                : (isset($data['incident_date']) ? Carbon::parse($data['incident_date']) : Carbon::now());

            $incident = HcmSafetyIncident::create([
                'tenant_id' => $data['tenant_id'],
                'company_id' => $data['company_id'] ?? null,
                'employee_id' => $data['employee_id'] ?? ($data['affected_employee_id'] ?? null),
                'incident_number' => $incidentNumber,
                'incident_datetime' => $dateTime,
                'incident_type' => $data['incident_type'],
                'severity' => $data['severity'] ?? ($data['severity_level'] ?? 'minor'),
                'location_description' => $data['location_description'] ?? ($data['title'] ?? 'On site'),
                'description' => $data['description'],
                'immediate_action_taken' => $data['immediate_actions_taken'] ?? ($data['immediate_action_taken'] ?? null),
                'status' => HcmSafetyIncident::STATUS_REPORTED,
                'is_osha_reportable' => $data['is_osha_reportable'] ?? ($data['is_osha_recordable'] ?? false),
                'lost_time_injury' => $data['lost_time_injury'] ?? ($data['is_lost_time'] ?? false),
                'lost_work_days' => $data['lost_work_days'] ?? 0,
                'reported_by' => $userId,
            ]);

            if (!empty($data['witnesses']) && is_array($data['witnesses'])) {
                foreach ($data['witnesses'] as $witnessData) {
                    $this->addWitness($incident, $witnessData);
                }
            }

            event(new SafetyIncidentReported($incident));

            return $incident->load('witnesses');
        });
    }

    /**
     * Add a witness to an incident.
     */
    public function addWitness(HcmSafetyIncident $incident, array $witnessData): HcmSafetyIncidentWitness
    {
        return HcmSafetyIncidentWitness::create([
            'tenant_id' => $incident->tenant_id,
            'safety_incident_id' => $incident->id,
            'employee_id' => $witnessData['employee_id'] ?? ($witnessData['witness_employee_id'] ?? null),
            'witness_name' => $witnessData['witness_name'] ?? ($witnessData['external_name'] ?? 'Witness'),
            'contact_phone' => $witnessData['contact_phone'] ?? ($witnessData['contact_info'] ?? null),
            'statement' => $witnessData['statement'] ?? null,
            'statement_date' => Carbon::today()->toDateString(),
        ]);
    }

    /**
     * Escalate severity level of an incident.
     */
    public function escalateSeverity(
        HcmSafetyIncident $incident,
        string $newSeverity,
        ?string $reason,
        int $userId
    ): HcmSafetyIncident {
        $oldSeverity = $incident->severity;

        if ($oldSeverity === $newSeverity) {
            return $incident;
        }

        $incident->update([
            'severity' => $newSeverity,
            'immediate_action_taken' => $incident->immediate_action_taken . "\n[Severity escalated from {$oldSeverity} to {$newSeverity}: {$reason}]",
        ]);

        event(new SafetyIncidentSeverityEscalated($incident, $oldSeverity, $newSeverity));

        return $incident->fresh();
    }


    /**
     * Close an incident after actions and investigation are finished.
     */
    public function closeIncident(
        HcmSafetyIncident $incident,
        ?string $closureNotes,
        int $userId
    ): HcmSafetyIncident {
        // Check if there are incomplete corrective actions
        $openActionsCount = $incident->actions()
            ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
            ->count();

        if ($openActionsCount > 0) {
            throw ValidationException::withMessages([
                'actions' => ["Cannot close incident: {$openActionsCount} corrective action(s) are still pending."],
            ]);
        }

        $incident->update([
            'status' => HcmSafetyIncident::STATUS_CLOSED,
            'closed_at' => Carbon::now(),
            'closed_by' => $userId,
            'root_cause_summary' => $closureNotes ?? $incident->root_cause_summary,
            'updated_by' => $userId,
        ]);

        event(new SafetyIncidentClosed($incident));

        return $incident->fresh();
    }

    /**
     * List safety incidents with filters and pagination.
     */
    public function listIncidents(string $tenantId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = HcmSafetyIncident::where('tenant_id', $tenantId)
            ->with(['affectedEmployee', 'location', 'department', 'actions', 'investigations']);

        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['severity_level'])) {
            $query->where('severity_level', $filters['severity_level']);
        }

        if (!empty($filters['incident_type'])) {
            $query->where('incident_type', $filters['incident_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('incident_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('incident_date', '<=', $filters['date_to']);
        }

        if (isset($filters['is_osha_recordable'])) {
            $query->where('is_osha_recordable', (bool) $filters['is_osha_recordable']);
        }

        return $query->orderBy('incident_date', 'desc')->paginate($perPage);
    }

    /**
     * Helper to generate unique incident tracking number.
     */
    protected function generateIncidentNumber(string $tenantId): string
    {
        $prefix = 'INC-' . Carbon::now()->format('Ymd') . '-';
        $count = HcmSafetyIncident::where('tenant_id', $tenantId)
            ->whereDate('created_at', Carbon::today())
            ->count() + 1;

        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
