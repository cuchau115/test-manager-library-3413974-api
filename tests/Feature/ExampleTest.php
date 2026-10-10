<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_returns_stateless_health_response_without_session_storage(): void
    {
        config(['session.driver' => 'file']);

        $response = $this->get('/');

        $response->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }
}
