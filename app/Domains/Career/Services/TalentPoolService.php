<?php

namespace App\Domains\Career\Services;

use App\Domains\Career\Events\TalentPoolCreated;
use App\Domains\Career\Events\TalentPoolMemberAdded;
use App\Domains\Career\Events\TalentPoolMemberRemoved;
use App\Domains\Career\Models\TalentPool;
use App\Domains\Career\Models\TalentPoolMember;
use App\Domains\Employee\Models\Employee;
use App\Domains\Shared\Services\AuditService;
use Illuminate\Support\Facades\DB;

class TalentPoolService
{
    public function __construct(protected AuditService $audit) {}

    public function createPool(
        string $tenantId,
        string $code,
        string $name,
        ?string $description = null,
        ?array $criteria = null,
        ?Employee $owner = null
    ): TalentPool {
        return DB::transaction(function () use ($tenantId, $code, $name, $description, $criteria, $owner) {
            $pool = TalentPool::query()->create([
                'tenant_id' => $tenantId,
                'code' => $code,
                'name' => $name,
                'description' => $description,
                'criteria' => $criteria,
                'owner_id' => $owner?->id,
                'status' => 'active',
            ]);

            TalentPoolCreated::dispatch($pool);

            $this->audit->record(
                $tenantId,
                'TalentPoolCreated',
                'create_talent_pool',
                TalentPool::class,
                (string) $pool->id,
                null,
                null,
                ['name' => $name, 'code' => $code]
            );

            return $pool;
        });
    }

    public function addMember(
        TalentPool $pool,
        Employee $employee,
        ?Employee $adder = null,
        ?string $reason = null
    ): TalentPoolMember {
        return DB::transaction(function () use ($pool, $employee, $adder, $reason) {
            $member = TalentPoolMember::query()->updateOrCreate(
                [
                    'tenant_id' => $pool->tenant_id,
                    'pool_id' => $pool->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'added_by' => $adder?->id,
                    'added_at' => now(),
                    'reason' => $reason,
                    'status' => 'active',
                ]
            );

            TalentPoolMemberAdded::dispatch($member);

            $this->audit->record(
                (string) $pool->tenant_id,
                'TalentPoolMemberAdded',
                'add_pool_member',
                TalentPoolMember::class,
                (string) $member->id,
                null,
                null,
                ['pool_id' => $pool->id, 'employee_id' => $employee->id, 'reason' => $reason]
            );

            return $member;
        });
    }

    public function removeMember(TalentPool $pool, Employee $employee): void
    {
        DB::transaction(function () use ($pool, $employee) {
            $member = TalentPoolMember::query()
                ->where('pool_id', $pool->id)
                ->where('employee_id', $employee->id)
                ->first();

            if ($member) {
                $member->delete();
                TalentPoolMemberRemoved::dispatch($member);

                $this->audit->record(
                    (string) $pool->tenant_id,
                    'TalentPoolMemberRemoved',
                    'remove_pool_member',
                    TalentPoolMember::class,
                    (string) $member->id,
                    null,
                    null,
                    ['pool_id' => $pool->id, 'employee_id' => $employee->id]
                );
            }
        });
    }
}
