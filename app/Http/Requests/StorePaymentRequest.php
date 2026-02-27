<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('add_payment');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'member_id' => ['required', Rule::exists('members', 'id')->whereNull('deleted_at')],
            'membership_id' => ['nullable', Rule::exists('memberships', 'id')->whereNull('deleted_at')],
            'payment_type' => ['required', 'in:membership,inventory,other'],
            'payment_method' => ['required', 'in:cash,card,transfer'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Member is required',
            'member_id.exists' => 'Selected member does not exist',
            'payment_type.required' => 'Payment type is required',
            'payment_method.required' => 'Payment method is required',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than or equal to 0',
            'payment_date.required' => 'Payment date is required',
        ];
    }
}
