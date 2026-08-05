<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileApiSecurityTest extends TestCase
{
    public function test_mobile_internal_admin_routes_are_not_publicly_exposed(): void
    {
        $this->postJson('/api/mobile/auth/register')->assertNotFound();
        $this->postJson('/api/mobile/pin/reset')->assertNotFound();
    }
}
