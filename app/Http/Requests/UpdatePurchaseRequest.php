<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product'     => 'required|string|max:200',
            'category'    => 'required|exists:categories,id',
            'supplier'    => 'required|exists:suppliers,id',
            'cost_price'  => 'required|numeric|min:0.01',
            'quantity'    => 'required|integer|min:1',
            'expiry_date' => 'required|date',
            'image'       => 'nullable|file|image|mimes:jpg,jpeg,png,gif|max:2048',
        ];
    }
}
