<?php

namespace DTOs;

class TransferInfoDTO
{
    public function __construct(public ?string $reference,
                                public ?\DateTime $date,
                                public ?float $amount,
                                public ?string $currency)
    {
    }

}
