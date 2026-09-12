<?php

namespace App\Domains\Learning\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'certificate_id', 'renewal_due_date', 'status', 'new_certificate_id'])]
class LearningCertificationRenewal extends LearningModel
{
    protected function casts(): array
    {
        return [
            'renewal_due_date' => 'date',
        ];
    }

    /** @return BelongsTo<LearningCertificate, LearningCertificationRenewal> */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(LearningCertificate::class, 'certificate_id');
    }

    /** @return BelongsTo<LearningCertificate, LearningCertificationRenewal> */
    public function newCertificate(): BelongsTo
    {
        return $this->belongsTo(LearningCertificate::class, 'new_certificate_id');
    }
}
