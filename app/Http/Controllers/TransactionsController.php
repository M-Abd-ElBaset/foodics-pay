<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendTransactionRequest;
use App\utilities\BankFactory;
use App\utilities\XmlBuilder;
use App\utilities\XmlDirector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionsController extends Controller
{
    public function __construct(protected XmlBuilder $xmlBuilder) {}

    public function receive(Request $request, string $bank) : JsonResponse
    {
        try {
            // Get transaction lines from request
            $content = $request->getContent() ?: ($request->input('content') ?? '');
            $lines = explode("\n", $content);
            $lines = array_filter($lines, fn($line) => !empty(trim($line)));

            // Parse transactions
            $bank = BankFactory::create($bank);

            $result = $bank->parse($lines);

            if (!empty($result['errors'])) {
                $processedCount = count($result['success']) + count($result['errors']);
                $failedCount = count($result['errors']);

                return response()->json([
                    'status' => 'partial',
                    'message' => 'Some transactions failed to parse',
                    'processed' => $processedCount,
                    'failed' => $failedCount,
                    'errors' => $result['errors'],
                    'transactions' => $result['success'],
                ], 207); // Multi-Status
            }

            return response()->json([
                'status' => 'success',
                'message' => 'All transactions processed successfully',
                'processed' => count($result['success']),
                'transactions' => $result['success'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function send(SendTransactionRequest $request)
    {
        $xmlDirector = new XmlDirector($this->xmlBuilder);
        $response = $xmlDirector->buildXmlFromTransactions($request);
        return response()->json($response);
    }
}
