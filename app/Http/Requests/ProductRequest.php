<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productKey = $product?->getKey();

        return [
            'product_name' => ['required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:255'],
            'price'        => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'cost_price'   => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'stock'        => ['required', 'integer', 'min:0'],
            'supplier'     => ['nullable', 'string', 'max:255'],
            'barcode'      => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')->ignore($productKey, 'product_id'),
            ],
            'date_added'     => ['nullable', 'date'],
            'product_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
