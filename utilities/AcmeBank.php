<?php

namespace utilities;

class AcmeBank extends Bank
{
    public function parseLine(string $transaction) : array
    {
        try {
            $parts = explode('//', $transaction);

            if (count($parts) !== 3) {
                throw new Exception('Invalid Acme transaction format');
            }

            $amount = trim($parts[0]);
            $reference = trim($parts[1]);
            $dateStr = trim($parts[2]);

            // Parse date (YYYYMMDD format)
            if (!preg_match('/^(\d{4})(\d{2})(\d{2})$/', $dateStr, $matches)) {
                throw new Exception('Invalid date format in Acme transaction');
            }

            $date = $matches[1] . '-' . $matches[2] . '-' . $matches[3]; // YYYY-MM-DD

            return [
                'bank' => 'acme',
                'date' => $date,
                'amount' => $amount,
                'reference' => $reference,
                'metadata' => [],
            ];
        } catch (Exception $e) {
            throw new Exception('Acme parsing error: ' . $e->getMessage());
        }
    }
}
