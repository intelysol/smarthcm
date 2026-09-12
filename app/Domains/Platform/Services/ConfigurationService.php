<?php

namespace App\Domains\Platform\Services;

use App\Domains\Platform\Contracts\TenantContext;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConfigurationService
{
    public function __construct(private readonly TenantContext $tenant) {}

    public function get(string $key, mixed $default = null, ?User $user = null, ?string $companyId = null): mixed { return $this->resolve($key, $user, $companyId) ?? $default; }
    public function has(string $key, ?User $user = null, ?string $companyId = null): bool { return $this->resolve($key, $user, $companyId) !== null; }
    public function resolve(string $key, ?User $user = null, ?string $companyId = null): mixed
    {
        $definition = DB::table('configuration_definitions')->where('key', $key)->first(); if ($definition === null) return null;
        $tenantId = $this->tenant->id() ?? $user?->tenant_id; $cacheKey = "configuration:{$key}:{$tenantId}:{$companyId}:".($user?->id ?? '');
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($definition, $tenantId, $companyId, $user) {
            $levels = array_filter([['user', $user?->id], ['company', $companyId], ['tenant', $tenantId], ['platform', null]], fn ($level) => $level[1] !== null || $level[0] === 'platform');
            foreach ($levels as [$scope, $scopeId]) { $value = DB::table('configuration_values')->where('definition_id', $definition->id)->where('scope', $scope)->where('scope_id', $scopeId)->first(); if ($value !== null) return $value->is_encrypted ? Crypt::decryptString(json_decode($value->value, true)) : json_decode($value->value, true); }
            return json_decode($definition->default_value, true);
        });
    }
    public function set(string $key, mixed $value, string $scope, ?string $scopeId, ?User $actor = null): void
    {
        $definition = DB::table('configuration_definitions')->where('key', $key)->firstOrFail(); $encrypted = (bool) $definition->is_sensitive; $stored = $encrypted ? json_encode(Crypt::encryptString(is_string($value) ? $value : json_encode($value))) : json_encode($value);
        DB::table('configuration_values')->updateOrInsert(['definition_id' => $definition->id, 'scope' => $scope, 'scope_id' => $scopeId], ['id' => (string) Str::uuid(), 'value' => $stored, 'is_encrypted' => $encrypted, 'changed_by' => $actor?->id, 'updated_at' => now(), 'created_at' => now()]);
        Cache::flush();
    }
}
