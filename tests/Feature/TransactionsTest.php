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
        $payload = "20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81\n" .
            "20250615156,75#202506159000002#note/service payment/internal_reference/A462JE82";

        $response = $this->post('/api/transactions/receive/foodics', [
            'content' => $payload
        ], [
            'CONTENT_TYPE' => 'text/plain',
        ]);

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
        $payload = "156,50//202506159000001//20250615\n" .
            "156,75//202506159000002//20250614";

        $response = $this->post('/api/transactions/receive/acme', [
            'content' => $payload
        ], [
            'CONTENT_TYPE' => 'text/plain',
        ]);

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
        $payload = "20250615156,50#REF001#note/valid/ref/test\n" .
            "INVALID_FORMAT";

        $response = $this->post('/api/transactions/foodics/receive', [
            'content' => $payload
        ], [
            'CONTENT_TYPE' => 'text/plain',
        ]);

        $response->assertStatus(207); // Multi-Status
        $response->assertJson([
            'status' => 'partial',
            'processed' => 2,
            'failed' => 1,
        ]);
    }

    public function test_send_transaction()
    {
        $payload = [
           'reference' => 'e0f4763d-28ea-42d4-ac1c-c4013c242105',
            'date' => '2036-09-25',
            'amount' => 177.39,
            'currency' => 'SAR',
            'sender_account' => 'SA6980000204608016212908',
            'bank_code' => 'FDCSSARI',
            'receiver_account' => 'SA6980000204608016211111',
            'beneficiary_name' => 'Jane Doe',
            'notes' => [
                'lorem ipsum',
                'lorem ipsum',
            ],
            'payment_type' => 421,
            'charge_details' => 'RB'
        ];

        $response = $this->postJson('/api/transactions/send', $payload);

        $response->assertStatus(200);
    }
}
