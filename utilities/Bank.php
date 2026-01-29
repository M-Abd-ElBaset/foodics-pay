<?php

namespace utilities;

interface Bank
{
    public function parse(string $transaction): array;
}
