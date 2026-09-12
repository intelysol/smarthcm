<?php
namespace App\Domains\Documents\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DocumentVersion extends Model
{
    use HasUuids;
    protected $fillable = ['document_id', 'version', 'storage_disk', 'storage_path', 'original_name', 'mime_type', 'size', 'checksum', 'change_notes', 'metadata', 'created_by', 'extracted_text'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function document(): BelongsTo { return $this->belongsTo(Document::class); }
}
