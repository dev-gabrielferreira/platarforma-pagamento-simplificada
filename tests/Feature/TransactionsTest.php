<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class TransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_transactions(): void
    {

        $response = $this->get(route('transfers.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'value', 'payer', 'payee', 'created_at', 'updated_at']
        ]);
    
    }

    public function test_make_transaction(): void
    {
        $user1 = User::create([
            "name" => 'user 1',
            "type" => 'usuario',
            "email" => 'user1@email.com',
            "cpf" => "178.248.297-07",
            "password" => bcrypt("12345678"),
            "balance" => 100
        ]);

        $shopkeeper = User::create([
            "name" => 'shopkeepr 1',
            "type" => 'lojista',
            "email" => 'shopkeeper1@email.com',
            "cpf" => "153.571.430-13",
            "password" => bcrypt("12345678"),
            "balance" => 0
        ]);

        $transfer = [
            "value" => 100,
            "payer" => $user1->id,
            "payee" => $shopkeeper->id
        ];

        $response = $this->post(route("transfers.store"), $transfer);
        $response->assertStatus(200);
        $response->assertJson([
            "message" => "Transferência realizada"
        ]);

        $user1 = User::find(1);
        $shopkeeper = User::find(2);

        assertEquals(0, $user1->balance);
        assertEquals(100, $shopkeeper->balance);

    }

    public function test_fail_transaction_with_insuficient_balance(): void
    {
        $user1 = User::create([
            "name" => 'user 1',
            "type" => 'usuario',
            "email" => 'user1@email.com',
            "cpf" => "178.248.297-07",
            "password" => bcrypt("12345678"),
            "balance" => 100
        ]);

        $shopkeeper = User::create([
            "name" => 'shopkeepr 1',
            "type" => 'lojista',
            "email" => 'shopkeeper1@email.com',
            "cpf" => "153.571.430-13",
            "password" => bcrypt("12345678"),
            "balance" => 0
        ]);

        $transfer = [
            "value" => 110,
            "payer" => $user1->id,
            "payee" => $shopkeeper->id
        ];

        $response = $this->post(route("transfers.store"), $transfer);
        $response->assertStatus(404);
        $response->assertJson([
            "message" => "Saldo insuficiente"
        ]);

        $user1 = User::find(1);
        $shopkeeper = User::find(2);

        assertEquals(100, $user1->balance);
        assertEquals(0, $shopkeeper->balance);
    }

    public function test_fail_transaction_with_unauthorized_type(): void
    {
        $user1 = User::create([
            "name" => 'user 1',
            "type" => 'usuario',
            "email" => 'user1@email.com',
            "cpf" => "178.248.297-07",
            "password" => bcrypt("12345678"),
            "balance" => 100
        ]);

        $shopkeeper = User::create([
            "name" => 'shopkeepr 1',
            "type" => 'lojista',
            "email" => 'shopkeeper1@email.com',
            "cpf" => "153.571.430-13",
            "password" => bcrypt("12345678"),
            "balance" => 0
        ]);

        $transfer = [
            "value" => 100,
            "payee" => $user1->id,
            "payer" => $shopkeeper->id
        ];

        $response = $this->post(route("transfers.store"), $transfer);
        $response->assertStatus(404);
        $response->assertJson([
            "message" => "Lojista não realizam transações"
        ]);

        $user1 = User::find(1);
        $shopkeeper = User::find(2);

        assertEquals(100, $user1->balance);
        assertEquals(0, $shopkeeper->balance);
    }

    public function test_fail_transaction_without_payer_or_payee(): void
    {
        $user1 = User::create([
            "name" => 'user 1',
            "type" => 'usuario',
            "email" => 'user1@email.com',
            "cpf" => "178.248.297-07",
            "password" => bcrypt("12345678"),
            "balance" => 100
        ]);

        $shopkeeper = User::create([
            "name" => 'shopkeepr 1',
            "type" => 'lojista',
            "email" => 'shopkeeper1@email.com',
            "cpf" => "153.571.430-13",
            "password" => bcrypt("12345678"),
            "balance" => 0
        ]);

        $transfer = [
            "value" => 100,
            "payee" => "",
            "payer" => $shopkeeper->id
        ];

        $response = $this->post(route("transfers.store"), $transfer);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(["payee"]);

        $user1 = User::find(1);
        $shopkeeper = User::find(2);

        assertEquals(100, $user1->balance);
        assertEquals(0, $shopkeeper->balance);

        $transfer = [
            "value" => 110,
            "payee" => $user1->id,
            "payer" => ""
        ];

        $response = $this->post(route("transfers.store"), $transfer);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(["payer"]);

        $user1 = User::find(1);
        $shopkeeper = User::find(2);

        assertEquals(100, $user1->balance);
        assertEquals(0, $shopkeeper->balance);
    }
}
