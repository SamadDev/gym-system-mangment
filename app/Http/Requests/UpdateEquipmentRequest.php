<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('edit_equipment');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $equipmentId = $this->route('equipment') ? $this->route('equipment')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('equipment', 'serial_number')->ignore($equipmentId)],
            'purchase_date' => ['required', 'date'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'warranty_expiry' => ['nullable', 'date', 'after:purchase_date'],
            'condition' => ['required', 'string', 'max:50'],
            'location' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Equipment name is required',
            'category.required' => 'Category is required',
            'serial_number.unique' => 'Serial number already exists',
            'purchase_date.required' => 'Purchase date is required',
            'purchase_price.required' => 'Purchase price is required',
            'purchase_price.min' => 'Purchase price must be greater than or equal to 0',
            'warranty_expiry.after' => 'Warranty expiry must be after purchase date',
            'condition.required' => 'Condition is required',
            'location.required' => 'Location is required',
        ];
    }
}
