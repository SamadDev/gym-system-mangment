<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('add_membership');
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', Rule::exists('members', 'id')->whereNull('deleted_at')],
            'membership_plan_id' => ['required', Rule::exists('membership_plans', 'id')->whereNull('deleted_at')],
            'start_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,card,bank_transfer,cheque'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
