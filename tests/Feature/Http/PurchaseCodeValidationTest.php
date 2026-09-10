<?php

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PurchaseCodeValidationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_short_code_is_rejected_by_validation(): void
    {
        $response = $this->post('/purchase-code', [
            '_token' => csrf_token(),
            'purchase_code' => 'too-short',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('purchase_code');
    }

    public function test_missing_code_is_rejected_by_validation(): void
    {
        $response = $this->post('/purchase-code', ['_token' => csrf_token()]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('purchase_code');
    }
}
