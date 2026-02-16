<?php

namespace App\DTOs;

class TransferInfoDTO
{
    public function __construct(public ?string $reference,
                                public ?string $date,
                                public ?float $amount,
                                public ?string $currency)
    {
    }

}
