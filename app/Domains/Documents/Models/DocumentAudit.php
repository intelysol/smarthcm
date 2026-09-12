<?php
namespace App\Domains\Documents\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class DocumentAudit extends Model
{
    use HasUuids;
    protected $fillable = ['document_id', 'user_id', 'action', 'metadata', 'ip_address', 'occurred_at'];
    protected function casts(): array { return ['metadata' => 'array', 'occurred_at' => 'datetime']; }
}
