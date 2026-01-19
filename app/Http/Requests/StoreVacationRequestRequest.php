<?php

namespace App\Http\Requests;

use App\Enum\VacationRequestType;
use App\Services\VacationBalanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVacationRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'type' => [
                'required',
                Rule::enum(VacationRequestType::class),
            ],
            'reason' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Please select a start date for your time off.',
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
            'end_date.required' => 'Please select an end date for your time off.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'type.required' => 'Please select the type of time off.',
            'reason.max' => 'The reason cannot exceed 500 characters.',
        ];
    }

    /**
     * Additional validation after basic rules pass.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $type = $this->input('type');

            if (! $startDate || ! $endDate || ! $type) {
                return;
            }

            $balanceService = app(VacationBalanceService::class);
            $startCarbon = Carbon::parse($startDate);
            $endCarbon = Carbon::parse($endDate);
            $typeEnum = VacationRequestType::from($type);

            // Check for overlapping requests
            $excludeId = $this->route('vacationRequest')?->id;
            if ($balanceService->hasOverlappingRequests($user, $startCarbon, $endCarbon, $excludeId)) {
                $validator->errors()->add(
                    'start_date',
                    'You already have a time off request for these dates. Please choose different dates.'
                );
            }

            // Check if user has sufficient balance
            if (! $balanceService->hasSufficientBalance($user, $startCarbon, $endCarbon, $typeEnum, $excludeId)) {
                $requestDays = $balanceService->calculateBusinessDays($startCarbon, $endCarbon);
                $balance = $balanceService->getBalanceSummary($user);

                $validator->errors()->add(
                    'end_date',
                    "Insufficient vacation balance. This request requires {$requestDays} business days, but you only have {$balance['available_balance']} days available (including pending requests)."
                );
            }
        });
    }
}
