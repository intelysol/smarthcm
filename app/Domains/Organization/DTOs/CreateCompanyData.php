<?php

namespace App\Domains\Organization\DTOs;

use App\Domains\Shared\DTOs\BaseData;

final readonly class CreateCompanyData extends BaseData
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public ?string $legalName,
        public ?string $registrationNumber,
        public ?string $taxNumber,
        public ?string $email,
        public ?string $phone,
        public ?string $website,
        public string $timezone,
        public string $currency,
        public int $actorId,
    ) {
    }

    /**
     * @param array{
     *     tenant_id: string,
     *     name: string,
     *     legal_name?: string|null,
     *     registration_number?: string|null,
     *     tax_number?: string|null,
     *     email?: string|null,
     *     phone?: string|null,
     *     website?: string|null,
     *     timezone?: string|null,
     *     currency?: string|null
     * } $data
     */
    public static function fromArray(array $data, int $actorId): self
    {
        return new self(
            tenantId: $data['tenant_id'],
            name: $data['name'],
            legalName: $data['legal_name'] ?? null,
            registrationNumber: $data['registration_number'] ?? null,
            taxNumber: $data['tax_number'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            website: $data['website'] ?? null,
            timezone: $data['timezone'] ?? 'UTC',
            currency: strtoupper($data['currency'] ?? 'USD'),
            actorId: $actorId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'name' => $this->name,
            'legal_name' => $this->legalName,
            'registration_number' => $this->registrationNumber,
            'tax_number' => $this->taxNumber,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'actor_id' => $this->actorId,
        ];
    }
}
