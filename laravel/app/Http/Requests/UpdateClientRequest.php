<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            // BlueSales дополнительные поля
            'full_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'vk_id' => 'nullable|string|max:100',
            'ok_id' => 'nullable|string|max:100',
            'crm_status' => 'nullable|string|max:100',
            'first_contact_date' => 'nullable|date',
            'next_contact_date' => 'nullable|date',
            'source' => 'nullable|string|max:100',
            'sales_channel' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
            'additional_contacts' => 'nullable|string',
        ];
    }
}