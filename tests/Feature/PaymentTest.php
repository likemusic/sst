<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use DatabaseTransactions;

    public function testBalance()
    {
        $response = $this->get('/balance?user=101');
        $response->assertStatus(200);
        $response->assertJson(['balance' => 1000]);
    }

    public function testDeposit()
    {
        $response = $this->json('POST', '/deposit', ['user' => 101, 'amount' => 100]);

        $response->assertStatus(200);
    }

    public function testWithdraw()
    {
        $response = $this->json('POST', '/withdraw', ['user' => 101, 'amount' => 50]);

        $response->assertStatus(200);
    }

    public function  testTransfer()
    {
        $response = $this->json('POST', '/transfer', ['from' => 101, 'to' => 205, 'amount' => 25]);

        $response->assertStatus(200);
    }
}
