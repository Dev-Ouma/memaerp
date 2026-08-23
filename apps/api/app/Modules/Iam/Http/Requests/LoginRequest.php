<?php

declare(strict_types=1);

namespace App\Modules\Iam\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('remember')) {
            $this->merge([
                'remember' => filter_var($this->input('remember'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'nullable', 'boolean'],
            'device_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
