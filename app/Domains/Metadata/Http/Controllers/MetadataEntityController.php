<?php

namespace App\Domains\Metadata\Http\Controllers;

use App\Domains\Metadata\Actions\CreateMetadataEntityAction;
use App\Domains\Metadata\DTOs\MetadataEntityData;
use App\Domains\Metadata\DTOs\MetadataFieldData;
use App\Domains\Metadata\Repositories\MetadataEntityRepository;
use App\Domains\Metadata\Requests\StoreMetadataEntityRequest;
use App\Domains\Metadata\Requests\StoreMetadataFieldRequest;
use App\Domains\Metadata\Resources\MetadataEntityResource;
use App\Domains\Metadata\Resources\MetadataFieldResource;
use App\Domains\Metadata\Services\MetadataCacheService;
use App\Domains\Metadata\Services\MetadataService;
use App\Domains\Platform\Contracts\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetadataEntityController
{
    public function __construct(private readonly MetadataEntityRepository $entities, private readonly CreateMetadataEntityAction $create, private readonly MetadataService $metadata, private readonly MetadataCacheService $cache, private readonly TenantContext $tenant) {}

    public function index(Request $request): JsonResponse
    {
        $this->allow($request, 'metadata.entities.view');

        return MetadataEntityResource::collection($this->entities->paginate($this->tenant->id(), $request->query()))->response();
    }

    public function store(StoreMetadataEntityRequest $request): JsonResponse
    {
        $entity = $this->create->execute($this->tenant->id(), $request->user(), MetadataEntityData::fromArray($request->validated()));

        return MetadataEntityResource::make($entity)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $entity): MetadataEntityResource
    {
        $this->allow($request, 'metadata.entities.view');

        return MetadataEntityResource::make($this->entities->find($this->tenant->id(), $entity));
    }

    public function addField(StoreMetadataFieldRequest $request, string $entity): JsonResponse
    {
        $model = $this->entities->find($this->tenant->id(), $entity);
        $this->allow($request, 'metadata.entities.manage');

        return MetadataFieldResource::make($this->metadata->addField($model, $request->user(), MetadataFieldData::fromArray($request->validated())))->response()->setStatusCode(201);
    }

    public function definition(Request $request, string $entity): JsonResponse
    {
        $this->allow($request, 'metadata.entities.view');

        return response()->json(['data' => $this->cache->definition($this->entities->find($this->tenant->id(), $entity))]);
    }

    public function storeRecord(Request $request, string $entity): JsonResponse
    {
        $model = $this->entities->find($this->tenant->id(), $entity);
        $this->allow($request, 'metadata.records.manage');

        return response()->json(['data' => $this->metadata->createRecord($model, $request->user(), $request->validate(['data' => ['required', 'array']])['data'])], 201);
    }

    private function allow(Request $request, string $permission): void
    {
        $request->user()->hasPermission($permission) || abort(403);
    }
}
