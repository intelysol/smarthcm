<?php

namespace App\Domains\Organization\Http\Controllers;

use App\Domains\Organization\DTOs\OrganizationEntityData;
use App\Domains\Organization\Requests\OrganizationEntityRequest;
use App\Domains\Organization\Resources\OrganizationEntityResource;
use App\Domains\Organization\Services\OrganizationEntityService;
use App\Domains\Organization\Services\OrganizationImportExportService;
use App\Domains\Organization\Support\OrganizationEntityRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrganizationEntityController extends Controller
{
    public function __construct(
        private readonly OrganizationEntityRegistry $registry,
        private readonly OrganizationEntityService $entities,
        private readonly OrganizationImportExportService $importExport,
    ) {
    }

    public function index(Request $request, string $entity): JsonResponse
    {
        $definition = $this->registry->get($entity);
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission("{$definition->permissionPrefix}.view"), 403);

        return response()->json(
            OrganizationEntityResource::collection(
                $this->entities->list($definition, (string) $user->tenant_id, $request->query()),
            )->response()->getData(true),
        );
    }

    public function store(OrganizationEntityRequest $request, string $entity): JsonResponse
    {
        $definition = $this->registry->get($entity);
        $user = $request->user();

        abort_if($user === null, 401);

        $record = $this->entities->create(
            $definition,
            OrganizationEntityData::fromArray($request->validated(), (string) $user->tenant_id, $user->id),
        );

        return OrganizationEntityResource::make($record)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $entity, string $id): JsonResponse
    {
        $definition = $this->registry->get($entity);
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission("{$definition->permissionPrefix}.view"), 403);

        return OrganizationEntityResource::make(
            $this->entities->find($definition, (string) $user->tenant_id, $id),
        )->response();
    }

    public function update(OrganizationEntityRequest $request, string $entity, string $id): JsonResponse
    {
        $definition = $this->registry->get($entity);
        $user = $request->user();

        abort_if($user === null, 401);

        $record = $this->entities->find($definition, (string) $user->tenant_id, $id);

        return OrganizationEntityResource::make(
            $this->entities->update(
                $definition,
                $record,
                OrganizationEntityData::fromArray($request->validated(), (string) $user->tenant_id, $user->id),
            ),
        )->response();
    }

    public function destroy(Request $request, string $entity, string $id): JsonResponse
    {
        $definition = $this->registry->get($entity);
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission("{$definition->permissionPrefix}.delete"), 403);

        $this->entities->delete(
            $this->entities->find($definition, (string) $user->tenant_id, $id),
            $user->id,
        );

        return response()->json(['message' => 'Record deleted successfully.']);
    }

    public function export(Request $request, string $entity): StreamedResponse
    {
        $definition = $this->registry->get($entity);
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission("{$definition->permissionPrefix}.export"), 403);

        $records = $this->entities->list($definition, (string) $user->tenant_id, $request->query())->items();

        return $this->importExport->export($definition, $records);
    }

    public function import(Request $request, string $entity): JsonResponse
    {
        $definition = $this->registry->get($entity);
        $user = $request->user();

        abort_if($user === null || ! $user->hasPermission("{$definition->permissionPrefix}.import"), 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        return response()->json([
            'imported' => $this->importExport->import($definition, $validated['file'], (string) $user->tenant_id, $user->id),
        ]);
    }
}
