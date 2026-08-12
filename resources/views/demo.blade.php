<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atlas SaaS Demo</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #e2e8f0;
            --ok: #15803d;
            --alert: #b45309;
            --accent: #0f766e;
            --bg: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, #ccfbf1 0%, transparent 40%),
                linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        main { max-width: 920px; margin: 0 auto; padding: 2.5rem 1.25rem 4rem; }
        h1 { font-size: 2rem; margin: 0 0 .5rem; letter-spacing: -0.03em; }
        p.lead { color: var(--muted); margin: 0 0 1.75rem; max-width: 40rem; line-height: 1.5; }
        .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .kpi {
            background: white;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1rem 1.1rem;
        }
        .kpi span { display: block; color: var(--muted); font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; }
        .kpi strong { font-size: 1.6rem; }
        .status-ok { color: var(--ok); }
        .status-alerta { color: var(--alert); }
        section {
            margin-top: 1.5rem;
            background: white;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1.25rem 1.4rem;
        }
        h2 { font-size: 1.1rem; margin: 0 0 .75rem; }
        code, pre {
            font-family: ui-monospace, Consolas, monospace;
            font-size: .85rem;
        }
        pre {
            background: #0f172a;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 10px;
            overflow-x: auto;
        }
        a { color: var(--accent); }
        ul { margin: .5rem 0 0; padding-left: 1.1rem; color: var(--muted); }
        .badge {
            display: inline-block;
            background: #ccfbf1;
            color: #115e59;
            border-radius: 999px;
            padding: .2rem .6rem;
            font-size: .75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
<main>
    <span class="badge">Portfolio demo · multi-tenant SaaS</span>
    <h1>Atlas SaaS Demo</h1>
    <p class="lead">
        Practice project inspired by production Colombian SaaS patterns:
        tenant isolation, module gating, inventory with weighted average cost,
        and Sanctum API. Original code — not a fork of any commercial product.
    </p>

    <div class="grid">
        <div class="kpi">
            <span>Status</span>
            <strong class="{{ $kpis['status'] === 'OK' ? 'status-ok' : 'status-alerta' }}">{{ $kpis['status'] }}</strong>
        </div>
        <div class="kpi"><span>SKUs</span><strong>{{ $kpis['sku_count'] }}</strong></div>
        <div class="kpi"><span>Low stock</span><strong>{{ $kpis['low_stock_count'] }}</strong></div>
        <div class="kpi"><span>Inventory value</span><strong>${{ number_format($kpis['inventory_value'], 0, '.', ',') }}</strong></div>
    </div>

    <section>
        <h2>Demo tenant</h2>
        <p><strong>{{ $company->name }}</strong> · {{ $company->slug }} · modules: {{ implode(', ', $modules) }}</p>
        <ul>
            @foreach ($products as $product)
                <li>{{ $product->sku }} — {{ $product->name }} · qty {{ $product->stock_qty }} · avg cost {{ number_format((float) $product->avg_cost, 2) }}</li>
            @endforeach
        </ul>
    </section>

    <section>
        <h2>Quick API smoke</h2>
        <pre>curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"demo@atlas.test\",\"password\":\"password\"}"

# then:
curl http://127.0.0.1:8000/api/dashboard \
  -H "Authorization: Bearer TOKEN" \
  -H "X-Company-Id: {{ $company->id }}" \
  -H "Accept: application/json"</pre>
        <p>Users: <code>demo@atlas.test</code> / <code>beta@atlas.test</code> · password <code>password</code></p>
    </section>

    <section>
        <h2>What this showcases</h2>
        <ul>
            <li><code>TenantContext</code> singleton + Eloquent <code>CompanyScope</code></li>
            <li>Module gate middleware (<code>inventario</code>)</li>
            <li>Stock receive/ship with weighted average cost</li>
            <li>Pest feature tests for tenant isolation</li>
            <li>Laravel 13 · PHP 8.3+ · Sanctum · SQLite</li>
        </ul>
    </section>
</main>
</body>
</html>
