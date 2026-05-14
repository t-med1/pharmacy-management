<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product'     => 'required|exists:purchases,id',
            'price'       => 'required|numeric|min:0.01',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'product.exists' => 'Please select a valid medicine from inventory.',
            'price.min'      => 'Selling price must be greater than zero.',
            'discount.max'   => 'Discount cannot exceed 100%.',
        ];
    }
}
