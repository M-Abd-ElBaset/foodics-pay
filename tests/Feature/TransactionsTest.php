<?php


// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionsTest extends TestCase
{
    /**
     * Test Foodics webhook endpoint with valid transactions
     */
    public function test_foodics_webhook_with_valid_transactions()
    {
        $payload = "20250615156,50.00#202506159000001#note/debt payment march/internal_reference/A462JE81\n" .
            "20250615156,75.50#202506159000002#note/service payment/internal_reference/A462JE82";

        $response = $this->post('/api/transactions/foodics/receive', [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'processed' => 2,
        ]);
    }

    /**
     * Test Acme webhook endpoint
     */
    public function test_acme_webhook_with_valid_transactions()
    {
        $payload = "50.00//202506159000001//20250615\n" .
            "75.50//202506159000002//20250614";

        $response = $this->post('/api/transactions/acme/receive', [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'processed' => 2,
        ]);
    }


    /**
     * Test webhook with partial errors
     */
    public function test_foodics_webhook_with_partial_errors()
    {
        $payload = "20250615156,50.00#REF001#note/valid/ref/test\n" .
            "INVALID_FORMAT";

        $response = $this->post('/api/transactions/foodics/receive', [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload);

        $response->assertStatus(207); // Multi-Status
        $response->assertJson([
            'status' => 'partial',
            'processed' => 2,
            'failed' => 1,
        ]);
    }
}
