<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('edit_inventory');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $inventoryItemId = $this->route('inventoryItem') ? $this->route('inventoryItem')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('inventory_items', 'sku')->ignore($inventoryItemId)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('inventory_items', 'barcode')->ignore($inventoryItemId)],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Item name is required',
            'category.required' => 'Category is required',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be greater than or equal to 0',
            'unit.required' => 'Unit is required',
            'cost_price.required' => 'Cost price is required',
            'selling_price.required' => 'Selling price is required',
            'reorder_level.required' => 'Reorder level is required',
            'sku.unique' => 'SKU already exists',
            'barcode.unique' => 'Barcode already exists',
        ];
    }
}
