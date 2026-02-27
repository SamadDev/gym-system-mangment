<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('edit_membership');
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:active,expired'],
            'end_date' => ['sometimes', 'date'],
        ];
    }
}
