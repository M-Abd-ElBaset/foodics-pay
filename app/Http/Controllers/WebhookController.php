<?php

namespace App\Http\Controllers;

use App\utilities\BankFactory;
use App\utilities\XmlBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(protected XmlBuilder $xmlBuilder) {}

    public function foodics(Request $request) : JsonResponse
    {
        try {
            // Get transaction lines from request
            $lines = explode("\n", $request->getContent());
            $lines = array_filter($lines, fn($line) => !empty(trim($line)));

            // Parse transactions
            $bank = BankFactory::create('foodics');

            $result = $bank->parse($lines);

            if (!empty($result['errors'])) {
                return response()->json([
                    'status' => 'partial',
                    'message' => 'Some transactions failed to parse',
                    'processed' => count($result['success']),
                    'failed' => count($result['errors']),
                    'errors' => $result['errors'],
                    'transactions' => $result['success'],
                ], 207); // Multi-Status
            }

            $bank->buildXmlFromTransactions($result['success']);

            return response()->json([
                'status' => 'success',
                'message' => 'All transactions processed successfully',
                'processed' => count($result['success']),
                'transactions' => $result['success'],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function acme(Request $request) : JsonResponse
    {
        try {
            $lines = explode("\n", $request->getContent());
            $lines = array_filter($lines, fn($line) => !empty(trim($line)));

            $bank = BankFactory::create('acme');
            $result = $bank->parse($lines);

            if (!empty($result['errors'])) {
                return response()->json([
                    'status' => 'partial',
                    'message' => 'Some transactions failed to parse',
                    'processed' => count($result['success']),
                    'failed' => count($result['errors']),
                    'errors' => $result['errors'],
                    'transactions' => $result['success'],
                ], 207);
            }

            $bank->buildXmlFromTransactions($result['success']);

            return response()->json([
                'status' => 'success',
                'message' => 'All transactions processed successfully',
                'processed' => count($result['success']),
                'transactions' => $result['success'],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
