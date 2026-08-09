<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskAssignmentRuleRequest extends FormRequest
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
        $taskAssignmentRule = $this->route('taskAssignmentRules');
        return [
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'rule_attribute' => [
                'required', 
                'string', 
                Rule::in(['department', 'minimum_experience', 'location', 'maximum_active_tasks']),
                Rule::unique('task_assignment_rules', 'rule_attribute')->where('task_id', $this->task_id)->ignore($taskAssignmentRule->id),
            ],
            'rule_operator' => [
                'required', 
                'string', 
                Rule::in(['=', '!=', '>', '<', '>=', '<=', 'IN'])
            ],
            'rule_value' => [
                'required', 
                'string'
            ],
        ];
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'rule_attribute.unique' => ':attribute has already been defined for this task.',
            'rule_attribute.in' => 'The selected rule attribute is invalid. Allowed values are: department, minimum_experience, location, maximum_active_tasks.',
            'rule_operator.in' => 'The selected rule operator is invalid. Allowed values are: =, >, <, >=, <=.',
            'rule_value.required' => 'The rule value is required.',
        ];
    }
}