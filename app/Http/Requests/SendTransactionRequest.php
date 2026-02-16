<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reference' => [
                'required',
                'string',
                'max:255',
            ],

            'date' => [
                'required',
                'date_format:Y-m-d'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999999.99',
            ],

            // Optional fields with defaults
            'currency' => [
                'nullable',
                'string',
                'size:3',  // ISO 4217 currency code (e.g., USD, EUR)
                'in:USD,EUR,GBP,AED,SAR,EGP',  // Whitelist supported currencies
            ],

            // Sender info
            'sender_account' => [
                'nullable',
                'string',
                'max:34',  // IBAN max length
                'regex:/^[A-Z0-9]+$/',
            ],

            // Receiver info
            'receiver_account' => [
                'nullable',
                'string',
                'max:34',
                'regex:/^[A-Z0-9]+$/',
                'required_with:bank_code',  // Must have if bank_code provided
            ],

            'bank_code' => [
                'nullable',
                'string',
                'max:11',  // SWIFT code max length
                'regex:/^[A-Z0-9]{6,11}$/',  // SWIFT/BIC format
                'required_with:receiver_account',  // Must have if receiver_account provided
            ],

            'beneficiary_name' => [
                'nullable',
                'string',
                'max:140',
                'min:3',
                'regex:/^[a-zA-Z\s\-\.\']+$/',  // Only letters, spaces, hyphens, dots, apostrophes
            ],
            'notes' =>  'nullable|array',
            'notes.*' => 'string|max:500',
            'payment_type' => 'nullable|integer',
            'charge_details' => 'nullable|string|max:3',
        ];
    }
}
