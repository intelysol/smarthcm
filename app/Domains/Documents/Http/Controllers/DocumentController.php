<?php
namespace App\Domains\Documents\Http\Controllers;
use App\Domains\Documents\Models\{Document, DocumentVersion};
use App\Domains\Documents\Requests\StoreDocumentRequest;
use App\Domains\Documents\Resources\DocumentResource;
use App\Domains\Documents\Services\DocumentService;
use App\Domains\Platform\Contracts\TenantContext;
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Support\Facades\Storage;
class DocumentController
{
    public function __construct(private readonly DocumentService $service, private readonly TenantContext $tenant) {}
    public function index(Request $request): JsonResponse { $this->allow($request, 'documents.view'); $query = Document::query()->where('tenant_id', $this->tenant->id())->when($request->query('search'), fn ($q, $value) => $q->where(fn ($inner) => $inner->whereLike('title', "%{$value}%")->orWhereLike('description', "%{$value}%")))->when($request->query('module'), fn ($q, $value) => $q->where('module', $value))->latest(); return DocumentResource::collection($query->paginate(min(max((int) $request->integer('per_page', 25), 1), 100)))->response(); }
    public function store(StoreDocumentRequest $request): JsonResponse { return DocumentResource::make($this->service->store($this->tenant->id(), $request->user(), $request->file('file'), $request->safe()->except('file')))->response()->setStatusCode(201); }
    public function show(Request $request, string $document): DocumentResource { $this->allow($request, 'documents.view'); return DocumentResource::make(Document::query()->where('tenant_id', $this->tenant->id())->with(['versions', 'audits'])->findOrFail($document)); }
    public function version(StoreDocumentRequest $request, string $document): JsonResponse { $this->allow($request, 'documents.manage'); $model = Document::query()->where('tenant_id', $this->tenant->id())->findOrFail($document); return DocumentResource::make($this->service->addVersion($model, $request->user(), $request->file('file'), $request->input('change_notes')))->response(); }
    public function status(Request $request, string $document): DocumentResource { $this->allow($request, 'documents.manage'); $model = Document::query()->where('tenant_id', $this->tenant->id())->findOrFail($document); $status = $request->validate(['status' => ['required', 'in:active,archived,restored']])['status']; return DocumentResource::make($this->service->setStatus($model, $request->user(), $status)); }
    public function download(Request $request, string $document, int $version = 0): Response { $this->allow($request, 'documents.view'); $model = Document::query()->where('tenant_id', $this->tenant->id())->findOrFail($document); $item = $model->versions()->where('version', $version ?: $model->current_version)->firstOrFail(); return Storage::disk($item->storage_disk)->download($item->storage_path, $item->original_name); }
    private function allow(Request $request, string $permission): void { $request->user()->hasPermission($permission) || abort(403); }
}
