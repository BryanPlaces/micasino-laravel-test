<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StorePaymentRequest extends FormRequest
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
            'pay-method' => ['required', 'string', 'max:255', Rule::in(['easymoney', 'superwalletz'])],
            'amount' => [
                'required',
                'numeric',
                Rule::when(
                    request()->input('pay-method') === 'easymoney',
                    ['integer']
                )
            ],
            'currency' => ['required', 'string', 'max:3', Rule::in(['USD', 'EUR'])],
        ];
    }

    public function messages(): array
    {
        return [
            'pay-method.required' => 'The payment method is required.',
            'pay-method.in' => 'The selected payment method is invalid.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be greater than 0.',
            'currency.required' => 'The currency is required.',
            'currency.in' => 'The selected currency is not supported.',
        ];
    }
}
