<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:tasks,title,except,id',
            'description' => 'nullable|string|max:255',
            'status' => 'sometimes|in:todo,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high',
        ];
    }
}