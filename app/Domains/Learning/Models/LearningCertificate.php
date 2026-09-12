<?php

namespace App\Domains\Learning\Models;

use App\Domains\Documents\Models\Document;
use App\Domains\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id', 'uuid', 'employee_id', 'course_id', 'course_version_id',
    'certificate_number', 'title', 'issued_at', 'expiry_date', 'status',
    'is_external', 'issuing_body', 'document_id', 'verification_code',
    'verified_at', 'verified_by'
])]
class LearningCertificate extends LearningModel
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expiry_date' => 'date',
            'is_external' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $cert): void {
            $cert->uuid ??= (string) Str::uuid();
            $cert->verification_code ??= strtoupper(Str::random(12));
        });
    }

    /** @return BelongsTo<Employee, LearningCertificate> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** @return BelongsTo<LearningCourse, LearningCertificate> */
    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'course_id');
    }

    /** @return BelongsTo<LearningCourseVersion, LearningCertificate> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(LearningCourseVersion::class, 'course_version_id');
    }

    /** @return BelongsTo<Document, LearningCertificate> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /** @return BelongsTo<User, LearningCertificate> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** @return HasMany<LearningCertificationRenewal> */
    public function renewals(): HasMany
    {
        return $this->hasMany(LearningCertificationRenewal::class, 'certificate_id');
    }
}
