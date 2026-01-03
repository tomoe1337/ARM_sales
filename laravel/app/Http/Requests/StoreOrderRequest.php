<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Убираем пробелы из числовых полей перед валидацией.
     */
    protected function prepareForValidation(): void
    {
        // Убираем пробелы из total_amount
        if ($this->has('total_amount') && is_string($this->total_amount)) {
            $this->merge([
                'total_amount' => str_replace(' ', '', $this->total_amount)
            ]);
        }

        // Убираем пробелы из цен в позициях заказа
        if ($this->has('order_items') && is_array($this->order_items)) {
            $orderItems = $this->order_items;
            foreach ($orderItems as $key => $item) {
                if (isset($item['price']) && is_string($item['price'])) {
                    $orderItems[$key]['price'] = str_replace(' ', '', $item['price']);
                }
            }
            $this->merge(['order_items' => $orderItems]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bluesales_id' => 'nullable|string|unique:orders,bluesales_id',
            'client_id' => 'required|exists:clients,id',
            'user_id' => 'nullable|exists:users,id',
            'deal_id' => 'nullable|exists:deals,id',
            'status' => 'required|string|in:new,reserve,preorder,shipped,delivered,cancelled',
            'internal_number' => 'nullable|string',
            'total_amount' => 'nullable|numeric|min:0', // Вычисляется автоматически из позиций
            'discount' => 'nullable|numeric|min:0|max:1',
            'prepay' => 'nullable|numeric|min:0',
            'customer_comments' => 'nullable|string',
            'internal_comments' => 'nullable|string',
            'order_items' => 'nullable|array',
            'order_items.*.product_name' => 'required_with:order_items|string',
            'order_items.*.product_marking' => 'nullable|string',
            'order_items.*.product_bluesales_id' => 'nullable|string',
            'order_items.*.price' => 'required_with:order_items|numeric|min:0',
            'order_items.*.quantity' => 'required_with:order_items|integer|min:1',
        ];
    }
}