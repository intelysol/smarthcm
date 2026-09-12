<?php
namespace App\Domains\Documents\Services;
use App\Domains\Documents\Models\{Document, DocumentAudit, DocumentVersion};
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class DocumentService
{
    public function store(string $tenantId, User $actor, UploadedFile $file, array $data): Document
    {
        $disk = (string) config('filesystems.default', 'local');
        $document = Document::query()->create([...$data, 'tenant_id' => $tenantId, 'title' => $data['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 'owner_id' => $actor->id, 'status' => 'active', 'current_version' => 1]);
        $path = $file->store("tenants/{$tenantId}/documents/{$document->id}", $disk);
        $document->versions()->create(['version' => 1, 'storage_disk' => $disk, 'storage_path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'size' => $file->getSize(), 'checksum' => hash_file('sha256', $file->getRealPath()), 'created_by' => $actor->id]);
        $this->audit($document, $actor, 'uploaded');
        return $document->load('versions');
    }
    public function addVersion(Document $document, User $actor, UploadedFile $file, ?string $notes = null): Document
    {
        $version = ((int) $document->versions()->max('version')) + 1;
        $disk = (string) config('filesystems.default', 'local');
        $path = $file->store("tenants/{$document->tenant_id}/documents/{$document->id}", $disk);
        $document->versions()->create(['version' => $version, 'storage_disk' => $disk, 'storage_path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'size' => $file->getSize(), 'checksum' => hash_file('sha256', $file->getRealPath()), 'change_notes' => $notes, 'created_by' => $actor->id]);
        $document->update(['current_version' => $version]);
        $this->audit($document, $actor, 'version_added', ['version' => $version]);
        return $document->load('versions');
    }
    public function setStatus(Document $document, User $actor, string $status): Document
    {
        abort_unless(in_array($status, ['active', 'archived', 'restored'], true), 422, 'Invalid document status.');
        $document->update(['status' => $status === 'restored' ? 'active' : $status]);
        $this->audit($document, $actor, $status);
        return $document;
    }
    private function audit(Document $document, User $actor, string $action, array $metadata = []): void { DocumentAudit::query()->create(['document_id' => $document->id, 'user_id' => $actor->id, 'action' => $action, 'metadata' => $metadata, 'occurred_at' => now()]); }
}
