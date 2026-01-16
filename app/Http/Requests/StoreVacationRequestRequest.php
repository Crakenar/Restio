<?php

namespace App\Http\Requests;

use App\Enum\VacationRequestStatus;
use App\Enum\VacationRequestType;
use App\Models\CompanySetting;
use App\Models\VacationRequest;
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

            // Check for overlapping requests
            if ($startDate && $endDate) {
                $overlapping = VacationRequest::query()
                    ->where('user_id', $user->id)
                    ->where('status', '!=', VacationRequestStatus::REJECTED->value)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($q) use ($startDate, $endDate) {
                                $q->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                            });
                    })
                    ->exists();

                if ($overlapping) {
                    $validator->errors()->add(
                        'start_date',
                        'You already have a time off request for these dates. Please choose different dates.'
                    );
                }
            }

            // Check annual days limit (only for vacation type)
            if ($type === VacationRequestType::VACATION->value && $startDate && $endDate) {
                $settings = CompanySetting::where('company_id', $user->company_id)->first();
                $annualDays = $settings ? $settings->annual_days : 25;

                // Calculate days for this request
                $requestDays = $this->calculateDays($startDate, $endDate);

                // Calculate already used days this year
                $usedDays = VacationRequest::query()
                    ->where('user_id', $user->id)
                    ->where('status', VacationRequestStatus::APPROVED->value)
                    ->where('type', VacationRequestType::VACATION->value)
                    ->whereYear('start_date', now()->year)
                    ->get()
                    ->sum(function ($request) {
                        return $this->calculateDays($request->start_date, $request->end_date);
                    });

                // Check if this request would exceed annual allowance
                if (($usedDays + $requestDays) > $annualDays) {
                    $remaining = $annualDays - $usedDays;
                    $validator->errors()->add(
                        'end_date',
                        "This request ({$requestDays} days) would exceed your annual allowance. You have {$remaining} days remaining."
                    );
                }
            }
        });
    }

    /**
     * Calculate the number of days between two dates (inclusive).
     */
    private function calculateDays(string|\DateTime $startDate, string|\DateTime $endDate): int
    {
        $start = is_string($startDate) ? new \DateTime($startDate) : $startDate;
        $end = is_string($endDate) ? new \DateTime($endDate) : $endDate;

        return $start->diff($end)->days + 1;
    }
}
