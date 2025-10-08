<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'bluesales_id' => 'required|string|unique:orders,bluesales_id,' . $this->route('order')->id,
            'client_id' => 'required|exists:clients,id',
            'user_id' => 'required|exists:users,id',
            'deal_id' => 'nullable|exists:deals,id',
            'status' => 'required|string|in:new,reserve,preorder,shipped,delivered,cancelled',
            'internal_number' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:1',
            'prepay' => 'nullable|numeric|min:0',
            'customer_comments' => 'nullable|string',
            'internal_comments' => 'nullable|string',
        ];
    }
}