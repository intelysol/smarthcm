<?php

declare(strict_types=1);

namespace Flow\Packages\Integrations\Infrastructure\Services;

use Flow\Packages\Integrations\Domain\Contracts\ConnectorInterface;
use Flow\Packages\Integrations\Infrastructure\Connectors\DemoHrConnector;
use Flow\Packages\Integrations\Infrastructure\Connectors\GenericRestConnector;
use Flow\Packages\Integrations\Infrastructure\Connectors\GenericWebhookConnector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ConnectorRegistry
{
    /**
     * @var array<string, ConnectorInterface>
     */
    protected array $connectors = [];

    public function __construct()
    {
        // Register standard out-of-the-box connectors
        $this->register(new GenericRestConnector());
        $this->register(new GenericWebhookConnector());
        $this->register(new DemoHrConnector());
    }

    public function register(ConnectorInterface $connector): void
    {
        $this->connectors[$connector->getKey()] = $connector;
    }

    public function get(string $key): ConnectorInterface
    {
        if (!isset($this->connectors[$key])) {
            throw new InvalidArgumentException("Connector [{$key}] is not registered.");
        }

        return $this->connectors[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->connectors[$key]);
    }

    /**
     * @return array<string, ConnectorInterface>
     */
    public function all(): array
    {
        return $this->connectors;
    }

    /**
     * Synchronize registered connectors to database table `integration_connectors`.
     */
    public function syncToDatabase(?string $tenantId = null): int
    {
        $count = 0;
        foreach ($this->connectors as $connector) {
            $manifest = $connector->getManifest();

            $data = [
                'name' => $connector->getName(),
                'connector_type' => $connector->getType(),
                'manifest' => json_encode($manifest),
                'status' => 'active',
                'updated_at' => now(),
            ];

            // If tenantId provided or global (null)
            $existing = DB::table('integration_connectors')
                ->where('key', $connector->getKey())
                ->first();

            if ($existing) {
                DB::table('integration_connectors')
                    ->where('id', $existing->id)
                    ->update($data);
            } else {
                $data['id'] = (string) Str::uuid();
                $data['key'] = $connector->getKey();
                if ($tenantId !== null) {
                    $data['tenant_id'] = $tenantId;
                }
                $data['created_at'] = now();
                DB::table('integration_connectors')->insert($data);
            }
            $count++;
        }

        return $count;
    }
}
