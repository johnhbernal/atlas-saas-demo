# Atlas SaaS Demo

Portfolio / practice project: a **multi-tenant inventory SaaS slice** built with **Laravel 13**, **PHP 8.3+**, **Sanctum**, and **Pest**.

Inspired by patterns used in production Colombian SaaS (tenant context, Eloquent global scope, module gating, weighted-average stock) — **original code**, MIT licensed.  
**Not affiliated with, and not a fork of, any commercial ERP product.**

## Why this repo exists

Showcase backend skills for recruiters:

| Skill | Where |
|--------|--------|
| Multi-tenant isolation | `TenantContext` + `CompanyScope` + `X-Company-Id` |
| Module entitlements | `EnsureModuleEnabled` + `config/modules.php` |
| Inventory domain | `StockService` (receive / ship, avg cost) |
| API auth | Laravel Sanctum |
| Tests | Pest feature tests (tenant isolation + stock math) |
| Demo UX | Light hub at `/` with OK / ALERTA KPIs |

## Stack

- Laravel 13 · PHP 8.3+
- SQLite (local / CI) — Postgres-ready schema
- Sanctum API tokens
- Pest 4

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)

### Demo users

| Email | Password | Companies |
|--------|----------|-----------|
| `demo@atlas.test` | `password` | Alpha + Beta |
| `beta@atlas.test` | `password` | Beta only |

## API

```bash
# Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@atlas.test","password":"password"}'

# Dashboard (tenant required)
curl http://127.0.0.1:8000/api/dashboard \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Company-Id: 1"

# Products (requires module inventario)
curl http://127.0.0.1:8000/api/products \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "X-Company-Id: 1" \
  -H "Accept: application/json"
```

| Method | Path | Notes |
|--------|------|--------|
| POST | `/api/login` | returns Sanctum token |
| GET | `/api/me` | auth |
| GET | `/api/dashboard` | KPIs for current tenant |
| GET/POST | `/api/products` | module `inventario` |
| POST | `/api/products/{id}/receive` | inbound + avg cost |
| POST | `/api/products/{id}/ship` | outbound |

## Tests

```bash
php artisan test
```

| Suite | Pins |
|-------|------|
| `TenantIsolationTest` | products are scoped to the authenticated tenant; foreign ids 404; a company header the user does not belong to is 403 |
| `StockServiceTest` | weighted-average cost on receive, stock decrease on ship, over-ship rejected |
| `ModuleGateTest` | module entitlement: no token 401, `inventario` missing or `enabled = false` → 403 with the module message, enabled → 200 |

## Definition of Done (Red-TIC mínimo)

1. Diff acotado al pedido
2. `php artisan test` verde (CI en push/PR)
3. Sin secretos en git
4. Tenant isolation intacta (`CompanyScope` / `X-Company-Id`)
5. Agentes: `.ai/AGENTS.md` + skill `redtic-software-practices`

Veredicto demo: **APTO CONDICIONAL** — no claim 100% calidad.

## Project map

```
app/
  Http/Middleware/SetTenantContext.php
  Http/Middleware/EnsureModuleEnabled.php
  Models/Scopes/CompanyScope.php
  Models/Concerns/BelongsToCompany.php
  Services/TenantContext.php
  Services/Inventory/StockService.php
config/modules.php
tests/Feature/TenantIsolationTest.php
tests/Feature/StockServiceTest.php
```

## Roadmap (practice)

- [ ] Filament admin panel (optional)
- [ ] React/TS POS client
- [ ] Docker Compose + Postgres
- [x] GitHub Actions CI (`.github/workflows/tests.yml` → `php artisan test`)

## Author

**John Hawer Bernal González** — Cali, Colombia · Remote LATAM  
Java/Spring · PHP/Laravel · AWS Certified Developer – Associate · English B2

## License

MIT
