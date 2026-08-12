<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Module catalog (demo)
    |--------------------------------------------------------------------------
    | Inspired by commercial SaaS module gating: base modules auto-enable,
    | add-ons are opt-in per company. Prices are fictional for portfolio use.
    */
    'catalog' => [
        'core' => [
            'name' => 'Core Hub',
            'category' => 'base',
            'price_usd' => 0,
        ],
        'inventario' => [
            'name' => 'Inventory & Stock',
            'category' => 'addon',
            'price_usd' => 49,
        ],
        'reportes' => [
            'name' => 'Operational Reports',
            'category' => 'addon',
            'price_usd' => 29,
        ],
    ],
];
