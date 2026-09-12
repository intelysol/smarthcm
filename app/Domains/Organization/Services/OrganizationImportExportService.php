<?php

namespace App\Domains\Organization\Services;

use App\Domains\Organization\DTOs\OrganizationEntityData;
use App\Domains\Organization\Support\OrganizationEntityDefinition;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrganizationImportExportService
{
    public function __construct(
        private readonly OrganizationEntityService $entities,
    ) {
    }

    public function export(OrganizationEntityDefinition $definition, iterable $records): StreamedResponse
    {
        return response()->streamDownload(function () use ($definition, $records): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, array_keys($definition->columns));

            foreach ($records as $record) {
                fputcsv($handle, collect(array_keys($definition->columns))->map(
                    fn (string $column): mixed => data_get($record, $column),
                )->all());
            }

            fclose($handle);
        }, "{$definition->key}.csv", ['Content-Type' => 'text/csv']);
    }

    public function import(OrganizationEntityDefinition $definition, UploadedFile $file, string $tenantId, int $actorId): int
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return 0;
        }

        $headers = fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $attributes = array_combine($headers ?: [], $row);

            if (! is_array($attributes)) {
                continue;
            }

            $validator = Validator::make($attributes, $definition->rules);
            $validator->validate();

            $this->entities->create(
                $definition,
                OrganizationEntityData::fromArray($validator->validated(), $tenantId, $actorId),
            );

            $imported++;
        }

        fclose($handle);

        return $imported;
    }
}
