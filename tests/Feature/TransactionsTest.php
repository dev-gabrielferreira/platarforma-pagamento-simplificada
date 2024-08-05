<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

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
}
