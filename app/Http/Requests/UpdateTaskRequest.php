<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $task = $this->route('task');
        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('tasks', 'title')->ignore($task),
            ],
            'description' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:todo,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high',
        ];
    }
}