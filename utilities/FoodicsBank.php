<?php

namespace utilities;

class FoodicsBank extends Bank
{
    public function parseLine(string $transaction) : array
    {
        try {
            // Split by # delimiter first
            $parts = explode('#', $transaction);

            if (count($parts) < 3) {
                throw new Exception('Invalid Foodics transaction format');
            }

            // Parse first part: Date and Amount
            $dateAmountPart = $parts[0];
            preg_match('/^(\d{4})(\d{2})(\d{2})(\d{3}),(\d+\.\d{2})$/', $dateAmountPart, $matches);

            if (empty($matches)) {
                throw new Exception('Invalid date/amount format in Foodics transaction');
            }

            $date = $matches[1] . '-' . $matches[2] . '-' . $matches[3]; // YYYY-MM-DD
            $amount = $matches[5];

            // Parse second part: Reference
            $reference = $parts[1];

            // Parse third part: Key-value pairs
            $keyValues = [];
            $kvPart = $parts[2];

            // Split by "/" to get key-value pairs
            $pairs = explode('/', $kvPart);

            // Group pairs (key/value/key/value...)
            for ($i = 0; $i < count($pairs); $i += 2) {
                if (isset($pairs[$i + 1])) {
                    $keyValues[$pairs[$i]] = $pairs[$i + 1];
                }
            }

            return [
                'bank' => 'foodics',
                'date' => $date,
                'amount' => $amount,
                'reference' => $reference,
                'metadata' => $keyValues,
                'note' => $keyValues['note'] ?? null,
                'internal_reference' => $keyValues['internal_reference'] ?? null,
            ];
        } catch (Exception $e) {
            throw new Exception('Foodics parsing error: ' . $e->getMessage());
        }
    }
}
