<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled in the controller
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
            'name' => ['required', 'string', 'max:255'],
            'annual_days' => ['required', 'integer', 'min:0', 'max:365'],
            'approval_required' => ['required', 'boolean'],
            'timezone' => ['required', 'string', 'timezone'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Company name is required.',
            'name.max' => 'Company name cannot exceed 255 characters.',
            'annual_days.required' => 'Annual vacation days is required.',
            'annual_days.integer' => 'Annual vacation days must be a number.',
            'annual_days.min' => 'Annual vacation days cannot be negative.',
            'annual_days.max' => 'Annual vacation days cannot exceed 365.',
            'approval_required.required' => 'Approval requirement setting is required.',
            'approval_required.boolean' => 'Approval requirement must be true or false.',
            'timezone.required' => 'Timezone is required.',
            'timezone.timezone' => 'Please select a valid timezone.',
        ];
    }
}
