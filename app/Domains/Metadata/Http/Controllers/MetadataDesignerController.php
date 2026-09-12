<?php

namespace App\Domains\Metadata\Http\Controllers;

use App\Domains\Metadata\Models\MetadataArtifact;
use App\Domains\Metadata\Models\MetadataEntity;
use App\Domains\Metadata\Models\MetadataRelationship;
use App\Domains\Metadata\Services\MetadataService;
use App\Domains\Platform\Contracts\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MetadataDesignerController
{
    public function __construct(private readonly MetadataService $metadata, private readonly TenantContext $tenant) {}

    public function artifacts(Request $request): JsonResponse
    {
        $this->allow($request, 'metadata.entities.view');

        return response()->json(['data' => MetadataArtifact::query()->where('tenant_id', $this->tenant->id())->when($request->query('type'), fn ($query, $type) => $query->where('artifact_type', $type))->orderByDesc('version')->get()]);
    }

    public function storeArtifact(Request $request): JsonResponse
    {
        $this->allow($request, 'metadata.artifacts.manage');
        $data = $request->validate(['artifact_type' => ['required', Rule::in(['form', 'view', 'layout', 'menu', 'component', 'validation'])], 'key' => ['required', 'alpha_dash', 'max:80'], 'name' => ['required', 'string', 'max:160'], 'description' => ['nullable', 'string', 'max:4000'], 'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])], 'definition' => ['required', 'array']]);

        return response()->json(['data' => $this->metadata->createArtifact($this->tenant->id(), $request->user(), $data)], 201);
    }

    public function transitionArtifact(Request $request, string $artifact): JsonResponse
    {
        $this->allow($request, 'metadata.artifacts.manage');
        $model = MetadataArtifact::query()->where('tenant_id', $this->tenant->id())->findOrFail($artifact);
        $status = $request->validate(['status' => ['required', Rule::in(['published', 'deprecated'])]])['status'];
        $result = $status === 'published' ? $this->metadata->publishArtifact($model, $request->user()) : $this->metadata->deprecateArtifact($model, $request->user());
        return response()->json(['data' => $result]);
    }

    public function relationships(Request $request): JsonResponse
    {
        $this->allow($request, 'metadata.entities.view');

        return response()->json(['data' => MetadataRelationship::query()->whereHas('source', fn ($query) => $query->where('tenant_id', $this->tenant->id()))->with(['source:id,key,label', 'target:id,key,label'])->get()]);
    }

    public function storeRelationship(Request $request): JsonResponse
    {
        $this->allow($request, 'metadata.entities.manage');
        $data = $request->validate(['source_entity_id' => ['required', 'uuid'], 'target_entity_id' => ['required', 'uuid', 'different:source_entity_id'], 'key' => ['required', 'alpha_dash', 'max:80'], 'relationship_type' => ['required', Rule::in(['one_to_one', 'one_to_many', 'many_to_many', 'hierarchical', 'self_referencing'])], 'source_field' => ['nullable', 'string', 'max:80'], 'target_field' => ['nullable', 'string', 'max:80'], 'configuration' => ['nullable', 'array']]);
        $source = MetadataEntity::query()->where('tenant_id', $this->tenant->id())->findOrFail($data['source_entity_id']);
        $target = MetadataEntity::query()->where('tenant_id', $this->tenant->id())->findOrFail($data['target_entity_id']);

        return response()->json(['data' => $this->metadata->createRelationship($source, $target, $request->user(), $data)], 201);
    }

    private function allow(Request $request, string $permission): void
    {
        $request->user()->hasPermission($permission) || abort(403);
    }
}
