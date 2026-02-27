<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('add_trainer');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:trainers,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:trainers,email'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'status' => ['nullable', Rule::in(['active', 'on_leave', 'terminated'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'phone.required' => 'Phone number is required',
            'phone.unique' => 'Phone number already exists',
            'email.unique' => 'Email already exists',
            'hire_date.required' => 'Hire date is required',
            'salary.min' => 'Salary must be greater than or equal to 0',
            'commission_rate.min' => 'Commission rate must be greater than or equal to 0',
            'commission_rate.max' => 'Commission rate must be less than or equal to 100',
        ];
    }
}
