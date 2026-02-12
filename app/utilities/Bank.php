<?php

namespace App\utilities;

use DTOs\ReceiverInfoDTO;
use DTOs\SenderInfoDTO;
use DTOs\TransferInfoDTO;
use utilities\Exception;

abstract class Bank
{
    abstract public function parseLine(string $transaction): array;

    public function parse(array $lines): array
    {
        $transactions = [];
        $errors = [];

        foreach ($lines as $index => $line) {
            try {
                if (!empty(trim($line))) {
                    $transactions[] = $this->parse($line);
                }
            } catch (Exception $e) {
                $errors[] = [
                    'line_index' => $index,
                    'line' => $line,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $transactions,
            'errors' => $errors,
        ];
    }
}
