<?php

it('renders the demo hub', function () {
    $this->seed(\Database\Seeders\DemoSeeder::class);

    $this->get('/')->assertOk()->assertSee('Atlas SaaS Demo');
});
