<?php

namespace App\utilities;

use DTOs\ReceiverInfoDTO;
use DTOs\SenderInfoDTO;
use DTOs\TransferInfoDTO;
use utilities\Exception;

abstract class Bank
{
    public function __construct(protected XmlBuilder $xmlBuilder){

    }
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


    public function buildXmlFromTransactions(array $transactions): string
    {
        $this->xmlBuilder->createDocument();

        foreach ($transactions as $transaction) {
            // Create Transfer Info DTO
            $currency = $transaction['currency'] ?? 'USD';

            $transferInfo = new TransferInfoDTO(
                $transaction['reference'],
                $transaction['date'],
                $transaction['amount'],
                $currency
            );

            $this->xmlBuilder->addTransferInfo($transferInfo);

            // Create Sender Info DTO if available
            if (!empty($transaction['sender_account'])) {
                $senderInfo = new SenderInfoDTO($transaction['sender_account']);
                $this->xmlBuilder->addSenderInfo($senderInfo);
            }

            // Create Receiver Info DTO if available
            if (!empty($transaction['receiver_account'])) {
                $receiverInfo = new ReceiverInfoDTO(
                    $transaction['bank_code'],
                    $transaction['receiver_account'],
                    $transaction['beneficiary_name']
                );
                $this->xmlBuilder->addReceiverInfo($receiverInfo);
            }

            // Add notes if available
            if (!empty($transaction['notes'])) {
                $notes = is_array($transaction['notes']) ? $transaction['notes'] : [$transaction['notes']];
                $this->xmlBuilder->addNotes($notes);
            }

            // Add payment type if available
            if (!empty($transaction['payment_type'])) {
                $this->xmlBuilder->addPaymentType($transaction['payment_type']);
            }

            // Add charge details if available
            if (!empty($transaction['charge_details'])) {
                $this->xmlBuilder->addChargeDetails($transaction['charge_details']);
            }
        }

        return $this->xmlBuilder->saveDocument();
    }
}
