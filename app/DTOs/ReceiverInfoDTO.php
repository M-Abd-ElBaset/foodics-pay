<?php

namespace App\DTOs;

class ReceiverInfoDTO
{
    public function __construct(public ?string $bankCode,
                                public ?string $accountNumber,
                                public ?string $beneficiaryName)
    {
    }

}
