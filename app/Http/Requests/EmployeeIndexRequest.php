<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class EmployeeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in(['all', 'active', 'inactive'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $query = $this->input('q');

        $this->merge([
            'q' => is_string($query) && trim($query) !== '' ? trim($query) : $query,
            'status' => $this->input('status', 'all'),
        ]);
    }
}
