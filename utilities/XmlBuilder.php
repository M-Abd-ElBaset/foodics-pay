<?php

namespace utilities;

use DOMDocument;
use DTOs\ReceiverInfoDTO;
use DTOs\SenderInfoDTO;
use DTOs\TransferInfoDTO;

class XmlBuilder
{
    protected DOMDocument $xml;
    protected \DOMElement $root;

    public function createDocument(): self
    {
        $this->xml = new DOMDocument();
        $this->root = $this->xml->createElement("PaymentRequestMessage");
        $this->xml->appendChild($this->root);
        return $this;
    }

    public function addTransferInfo(TransferInfoDTO $dto): self
    {
        $transferInfo = $this->xml->createElement('TransferInfo');

        if($dto->reference)
        {
            $transferInfo->appendChild($this->xml->createElement('Reference', $dto->reference));
        }
        if($dto->date)
        {
            $transferInfo->appendChild($this->xml->createElement('Date', $dto->date));
        }

        if($dto->amount)
        {
            $transferInfo->appendChild($this->xml->createElement('Amount', $dto->amount));
        }

        if($dto->currency)
        {
            $transferInfo->appendChild($this->xml->createElement('Currency', $dto->currency));
        }

        $this->root->appendChild($transferInfo);

        return $this;
    }

    public function addSenderInfo(SenderInfoDTO $dto): self
    {
        $senderInfo = $this->xml->createElement('SenderInfo');

        if($dto->accountNumber)
        {
            $senderInfo->appendChild($this->xml->createElement('AccountNumber', $dto->accountNumber));
        }

        $this->xml->appendChild($senderInfo);

        return $this;
    }

    public function addReceiverInfo(ReceiverInfoDTO $dto): self
    {
        $receiverInfo = $this->xml->createElement('ReceiverInfo');

        if($dto->bankCode)
        {
            $receiverInfo->appendChild($this->xml->createElement('BankCode', $dto->bankCode));
        }

        if($dto->accountNumber)
        {
            $receiverInfo->appendChild($this->xml->createElement('AccountNumber', $dto->accountNumber));
        }

        if($dto->beneficiaryName)
        {
            $receiverInfo->appendChild($this->xml->createElement('BeneficiaryName', $dto->beneficiaryName));
        }

        $this->root->appendChild($receiverInfo);

        return $this;
    }

    public function addNotes(array $notes): self
    {
        $notes = $this->xml->createElement('Notes');

        foreach($notes as $note)
        {
            $notes->appendChild($this->xml->createElement('Note', $note));
        }

        $this->root->appendChild($notes);

        return $this;
    }

    public function addPaymentType(string $paymentType): self
    {
        $this->root->appendChild($this->xml->createElement('PaymentType'), $paymentType);

        return $this;
    }

    public function addChargeDetails(string $chargeDetails): self
    {
        $this->root->appendChild($this->xml->createElement('ChargeDetails'), $chargeDetails);

        return $this;
    }

    public function saveDocument(): string
    {
        $this->xml->save("request.xml");
        return $this->xml->saveXML();
    }

}
