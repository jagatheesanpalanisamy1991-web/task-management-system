<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department' => 'sometimes|string|in:finance,hr,it,operation',
            'location' => 'sometimes|string',
            'years_experience' => 'sometimes|integer|min:0',
        ];
    }
}