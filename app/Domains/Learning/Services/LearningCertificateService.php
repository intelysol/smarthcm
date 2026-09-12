<?php

namespace App\Domains\Learning\Services;

use App\Domains\Employee\Models\Employee;
use App\Domains\Events\Services\EventBus;
use App\Domains\Learning\Events\LearningCertificateExpired;
use App\Domains\Learning\Events\LearningCertificateExpiring;
use App\Domains\Learning\Events\LearningCertificateIssued;
use App\Domains\Learning\Events\LearningCertificateRevoked;
use App\Domains\Learning\Models\LearningCertificate;
use App\Domains\Learning\Models\LearningCertificationRenewal;
use App\Domains\Learning\Models\LearningCourse;
use App\Domains\Learning\Models\LearningCourseVersion;
use App\Domains\Learning\Models\LearningEnrollment;
use App\Domains\Shared\Services\AuditService;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LearningCertificateService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly EventBus $events
    ) {}

    public function issueCertificate(
        Employee $employee,
        LearningCourse $course,
        ?LearningCourseVersion $version = null,
        ?LearningEnrollment $enrollment = null,
        ?Carbon $expiryDate = null
    ): LearningCertificate {
        // Generate unique certificate number
        $year = now()->format('Y');
        $random = strtoupper(Str::random(8));
        $certNumber = "CERT-{$year}-{$course->code}-{$random}";

        return DB::transaction(function () use ($employee, $course, $version, $enrollment, $certNumber, $expiryDate) {
            $certificate = LearningCertificate::query()->create([
                'tenant_id' => $employee->tenant_id,
                'employee_id' => $employee->id,
                'course_id' => $course->id,
                'course_version_id' => $version?->id,
                'certificate_number' => $certNumber,
                'title' => "Certificate of Completion: {$course->title}",
                'issued_at' => now()->toDateString(),
                'expiry_date' => $expiryDate?->toDateString(),
                'status' => 'active',
                'is_external' => false,
                'verification_code' => strtoupper(Str::random(12)),
            ]);

            LearningCertificateIssued::dispatch($certificate);

            $this->audit->record(
                (string) $employee->tenant_id,
                'LearningCertificateIssued',
                'issue_certificate',
                LearningCertificate::class,
                (string) $certificate->id,
                null,
                null,
                [
                    'certificate_number' => $certNumber,
                    'course_id' => $course->id,
                    'employee_id' => $employee->id,
                ]
            );

            return $certificate;
        });
    }

    public function verifyCertificate(string $tenantId, string $certificateNumber, ?string $verificationCode = null): ?LearningCertificate
    {
        $query = LearningCertificate::query()
            ->where('tenant_id', $tenantId)
            ->where('certificate_number', $certificateNumber);

        if ($verificationCode) {
            $query->where('verification_code', $verificationCode);
        }

        return $query->with(['employee', 'course', 'version'])->first();
    }

    public function revokeCertificate(User $actor, LearningCertificate $certificate, string $reason): LearningCertificate
    {
        return DB::transaction(function () use ($actor, $certificate, $reason) {
            $certificate->update(['status' => 'revoked']);

            LearningCertificateRevoked::dispatch($certificate);

            $this->audit->record(
                (string) $certificate->tenant_id,
                'LearningCertificateRevoked',
                'revoke_certificate',
                LearningCertificate::class,
                (string) $certificate->id,
                $actor->id,
                null,
                ['reason' => $reason]
            );

            return $certificate;
        });
    }

    public function expireCertificate(LearningCertificate $certificate): LearningCertificate
    {
        return DB::transaction(function () use ($certificate) {
            $certificate->update(['status' => 'expired']);

            // Create renewal record
            LearningCertificationRenewal::query()->create([
                'tenant_id' => $certificate->tenant_id,
                'certificate_id' => $certificate->id,
                'renewal_due_date' => now()->addDays(30)->toDateString(),
                'status' => 'pending',
            ]);

            LearningCertificateExpired::dispatch($certificate);

            return $certificate;
        });
    }

    public function renewCertificate(User $actor, LearningCertificate $oldCert, array $data): LearningCertificate
    {
        return DB::transaction(function () use ($actor, $oldCert, $data) {
            $newCert = $this->issueCertificate(
                $oldCert->employee,
                $oldCert->course,
                $oldCert->version,
                null,
                isset($data['expiry_date']) ? Carbon::parse($data['expiry_date']) : null
            );

            $oldCert->update(['status' => 'renewed']);

            LearningCertificationRenewal::query()
                ->where('certificate_id', $oldCert->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'completed',
                    'new_certificate_id' => $newCert->id,
                ]);

            return $newCert;
        });
    }
}
