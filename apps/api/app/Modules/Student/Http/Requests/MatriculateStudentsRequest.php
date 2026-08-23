<?php

declare(strict_types=1);

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class MatriculateStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('student.record.matriculate');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'application_ids' => ['required', 'array', 'min:1', 'max:50'],
            'application_ids.*' => ['required', 'uuid', 'distinct'],
            'pledge_signed' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
