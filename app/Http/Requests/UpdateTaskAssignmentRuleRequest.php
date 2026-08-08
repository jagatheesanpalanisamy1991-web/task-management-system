<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreTaskAssignmentRuleRequest extends FormRequest
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
        $taskAssignmentRule = $this->route('taskAssignmentRule');
        return [
            'task_id' => ['required', 'integer', 'exists:tasks,id',Rule::unique('task_assignment_rules', 'task_id')
                    ->ignore($taskAssignmentRule)],
            'department' => ['required', Rule::in(['finance', 'hr', 'it', 'operation'])],
            'location' => ['required', 'string', 'max:255'],
            'minimum_experience' => ['required', 'integer', 'min:0', 'max:50'],
            'maximum_active_tasks' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'task_id.unique' => 'Eligibility rules have already been configured for this task.',
            'department.in' => 'The selected department is invalid.',
            'minimum_experience.max' => 'Experience cannot exceed 50 years.',
            'maximum_active_tasks.min' => 'Maximum active tasks must be at least 1.',
        ];
    }
}