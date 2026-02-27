<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Enums\BloodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('add_member');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:members,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:members,email'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'emergency_contact' => ['required', 'string', 'max:255'],
            'emergency_phone' => ['required', 'string', 'max:20'],
            'blood_type' => ['nullable', Rule::enum(BloodType::class)],
            'status' => ['required', Rule::enum(MemberStatus::class)],
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
            'phone.unique' => 'This phone number is already registered',
            'email.unique' => 'This email is already registered',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.before' => 'Date of birth must be before today',
            'gender.required' => 'Gender is required',
            'emergency_contact.required' => 'Emergency contact name is required',
            'emergency_phone.required' => 'Emergency contact phone is required',
            'photo.image' => 'Photo must be an image',
            'photo.max' => 'Photo size must not exceed 2MB',
        ];
    }
}
