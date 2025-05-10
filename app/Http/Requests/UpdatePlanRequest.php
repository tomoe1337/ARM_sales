<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
use App\Models\User;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        $plan = $user->plan;

        if (!$plan) {
             // Если плана для пользователя нет, возможно, авторизация не требуется
             // или должна быть другая логика. Здесь просто вернем true
             // или false в зависимости от общего правила.
             return Auth::check();
        }

        return $this->authorize('update', $plan);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'monthly_plan' => 'required|numeric|min:0',
            'daily_plan' => 'required|numeric|min:0',
        ];
    }
}