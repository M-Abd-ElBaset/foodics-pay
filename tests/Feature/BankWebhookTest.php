<?php


// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankWebhookTest extends TestCase
{
    /**
     * Test Foodics webhook endpoint with valid transactions
     */
    public function test_foodics_webhook_with_valid_transactions()
    {
        $payload = "20250615156,50.00#202506159000001#note/debt payment march/internal_reference/A462JE81\n" .
            "20250615156,75.50#202506159000002#note/service payment/internal_reference/A462JE82";

        $response = $this->post('/api/webhooks/foodics', [], [
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

        $response = $this->post('/api/webhooks/acme', [], [
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
    public function test_webhook_with_partial_errors()
    {
        $payload = "20250615156,50.00#REF001#note/valid/ref/test\n" .
            "INVALID_FORMAT";

        $response = $this->post('/api/webhooks/foodics', [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload);

        $response->assertStatus(207); // Multi-Status
        $response->assertJson([
            'status' => 'partial',
            'processed' => 2,
            'failed' => 1,
        ]);
    }

    /**
     * Test transfer generation endpoint
     */
    public function test_generate_transfer_from_foodics_transaction()
    {
        $response = $this->postJson('/api/transfers/generate', [
            'transaction_line' => '20250615156,50.00#202506159000001#note/debt payment/internal_reference/A462JE81',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);
        $response->assertJsonStructure([
            'status',
            'transaction' => [
                'bank',
                'date',
                'amount',
                'reference',
                'metadata',
            ],
            'xml',
        ]);

        // Verify XML structure
        $this->assertStringContainsString('BankTransferRequest', $response['xml']);
        $this->assertStringContainsString('202506159000001', $response['xml']);
        $this->assertStringContainsString('50.00', $response['xml']);
    }

    /**
     * Test transfer generation with Acme transaction
     */
    public function test_generate_transfer_from_acme_transaction()
    {
        $response = $this->postJson('/api/transfers/generate', [
            'transaction_line' => '75.50//REF12345//20250615',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'transaction' => [
                'bank' => 'acme',
                'amount' => '75.50',
                'reference' => 'REF12345',
                'date' => '2025-06-15',
            ],
        ]);
    }

    /**
     * Test batch transfer generation
     */
    public function test_generate_batch_transfer()
    {
        $response = $this->postJson('/api/transfers/batch', [
            'transactions' => [
                '20250615156,50.00#REF001#note/payment 1/ref/REF001',
                '75.50//REF002//20250615',
                '20250615156,100.00#REF003#note/payment 3/status/pending',
            ],
            'batch_id' => 'BATCH-2025-TEST',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'count' => 3,
        ]);

        // Verify XML contains all transactions
        $this->assertStringContainsString('BankTransferBatch', $response['xml']);
        $this->assertStringContainsString('BATCH-2025-TEST', $response['xml']);
        $this->assertStringContainsString('count="3"', $response['xml']);
        $this->assertStringContainsString('REF001', $response['xml']);
        $this->assertStringContainsString('REF002', $response['xml']);
        $this->assertStringContainsString('REF003', $response['xml']);
    }

    /**
     * Test batch transfer with default batch ID
     */
    public function test_generate_batch_transfer_without_batch_id()
    {
        $response = $this->postJson('/api/transfers/batch', [
            'transactions' => [
                '50.00//REF001//20250615',
                '75.50//REF002//20250615',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'count' => 2,
        ]);

        // Should have auto-generated batch ID
        $this->assertStringContainsString('BATCH-', $response['xml']);
    }

    /**
     * Test validation error handling
     */
    public function test_transfer_generation_with_invalid_data()
    {
        $response = $this->postJson('/api/transfers/generate', [
            'transaction_line' => 'INVALID_FORMAT',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
        ]);
    }

    /**
     * Test missing required field
     */
    public function test_transfer_generation_with_missing_field()
    {
        $response = $this->postJson('/api/transfers/generate', []);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test batch with empty transactions
     */
    public function test_batch_transfer_with_empty_array()
    {
        $response = $this->postJson('/api/transfers/batch', [
            'transactions' => [],
        ]);

        $response->assertStatus(422); // Validation error
    }

    /**
     * Test large batch processing
     */
    public function test_batch_processing_with_many_transactions()
    {
        $transactions = [];
        for ($i = 1; $i <= 100; $i++) {
            $transactions[] = "50.00//REF" . str_pad($i, 5, '0', STR_PAD_LEFT) . "//20250615";
        }

        $response = $this->postJson('/api/transfers/batch', [
            'transactions' => $transactions,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'count' => 100,
        ]);
    }

    /**
     * Test XML contains proper date format
     */
    public function test_generated_xml_has_correct_date_format()
    {
        $response = $this->postJson('/api/transfers/generate', [
            'transaction_line' => '20250615156,50.00#REF001#note/test',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('2025-06-15', $response['xml']);
    }

    /**
     * Test XML escapes special characters
     */
    public function test_generated_xml_escapes_special_characters()
    {
        $response = $this->postJson('/api/transfers/generate', [
            'transaction_line' => '20250615156,50.00#REF001#note/test & <special> "chars"',
        ]);

        $response->assertStatus(200);
        // Should not contain unescaped special chars
        $xml = $response['xml'];
        $this->assertStringNotContainsString('&<', $xml);
    }

    /**
     * Test webhook response includes error details
     */
    public function test_webhook_error_response_includes_details()
    {
        $payload = "INVALID_LINE_1\nINVALID_LINE_2";

        $response = $this->post('/api/webhooks/bank', [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $payload);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
        ]);
        $this->assertArrayHasKey('message', $response->json());
    }

    /**
     * Test timezone in XML timestamp
     */
    public function test_xml_includes_timestamp()
    {
        $response = $this->postJson('/api/transfers/generate', [
            'transaction_line' => '20250615156,50.00#REF001#note/test',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('timestamp=', $response['xml']);
    }
}
