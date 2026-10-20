# AGENTS — Atlas SaaS Demo (orquestación)

> Portfolio / práctica multi-tenant (Laravel). **No** producto comercial Kilele.
> Piso transversal: skill personal `redtic-software-practices` ([Red-TIC UNAM](https://www.red-tic.unam.mx/desarrollo-de-software)).

## Identidad

| | |
|---|---|
| Stack | Laravel 13 · PHP 8.3+ · Sanctum · Pest · SQLite |
| Propósito | Showcase tenant isolation + stock + módulos |
| Relación | Código original MIT; patrones inspirados, no fork de ERP comercial |

## Gates (mínimo Red-TIC)

1. Alcance del diff = lo pedido
2. `php artisan test` en verde (local + CI `tests.yml`)
3. Sin secretos en git
4. Multi-tenant: no romper `CompanyScope` / `TenantContext` / `X-Company-Id`
5. Docs solo si el usuario lo pide o cambia el contrato público

## Mejora continua

Tras cada lote: 1 deuda técnica + 1 siguiente paso. No claim «100% Red-TIC».

## Status

Veredicto: **APTO CONDICIONAL** (demo). CI Pest existe; paridad Filament/POS no es alcance.