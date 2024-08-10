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
            '*' => ['id', 'balance', 'name', 'cpf', 'email', 'type', 'created_at', 'updated_at']
        ]);

    }

    public function test_create_user(): void
    {
        $data = [
            "name" => "user 1",
            "email" => "user1@email.com",
            "type" => "usuario",
            "cpf" => "153.571.430-13",
            "password" => "12345678",
            "balance" => 0
        ];

        $response = $this->post(route("users.store"), $data);
        $response->assertStatus(201);
        $response->assertJson([
            "message" => "Usuario criado"
        ]);

        $data_repeated_email = [
            "name" => "user 2",
            "email" => "user1@email.com",
            "type" => "usuario",
            "cpf" => "512.718.540-36",
            "password" => "12345678",
            "balance" => 0
        ];

        $response = $this->post(route("users.store"), $data_repeated_email);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        $data_repeated_cpf = [
            "name" => "user 2",
            "email" => "user2@email.com",
            "type" => "usuario",
            "cpf" => "153.571.430-13",
            "password" => "12345678",
            "balance" => 0
        ];

        $response = $this->post(route("users.store"), $data_repeated_cpf);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['cpf']);
        
        $data_without_name = [
            "name" => "",
            "email" => "user2@email.com",
            "type" => "usuario",
            "cpf" => "178.248.297.07",
            "password" => "12345678",
            "balance" => 0
        ];

        $response = $this->post(route("users.store"), $data_without_name);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }
}
