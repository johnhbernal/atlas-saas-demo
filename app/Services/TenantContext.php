<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;

/**
 * Request-scoped tenant holder (singleton).
 * Pattern used in production multi-tenant SaaS: set once per request, read everywhere.
 */
final class TenantContext
{
    private ?Company $company = null;

    public function set(?Company $company): void
    {
        $this->company = $company;
    }

    public function clear(): void
    {
        $this->company = null;
    }

    public function company(): ?Company
    {
        return $this->company;
    }

    public function companyId(): ?int
    {
        return $this->company?->id;
    }

    public function hasCompany(): bool
    {
        return $this->company !== null;
    }

    public function idOrFail(): int
    {
        if ($this->company === null) {
            throw new \RuntimeException('Tenant context is not set.');
        }

        return $this->company->id;
    }
}
