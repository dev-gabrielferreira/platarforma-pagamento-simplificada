<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_of_users(): void
    {

        $response = $this->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'name', 'cpf', 'email', 'type', 'created_at', 'updated_at']
        ]);

    }
}
