<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|in:Finance,HR,IT,Marketing',
            'position' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:employees,email,' . $employeeId,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:255',
        ];
    }
}
