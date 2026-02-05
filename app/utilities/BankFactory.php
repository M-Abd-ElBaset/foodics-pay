<?php

namespace App\utilities;

class BankFactory
{
    const BANKS = ['foodics', 'acme'];
    public function create($name) : Bank
    {

        if(!in_array($name, self::BANKS)){
            throw new \Exception("Invalid Bank");
        }

        return match($name) {
            'foodics' => new FoodicsBank(),
            'acme' => new AcmeBank(),
        };
    }
}
