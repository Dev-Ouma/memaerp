<?php

declare(strict_types=1);

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

final class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('student.record.update');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'current_year_level' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'current_semester' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'academic_standing' => ['sometimes', 'string', 'in:GOOD_STANDING,PROBATION,SUSPENDED,DISCONTINUED'],
            'status' => ['sometimes', 'string', 'in:ACTIVE,ON_LEAVE,SUSPENDED,GRADUATED,WITHDRAWN'],
            'change_reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'change_reason.required' => 'A change reason is required for the audit trail.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->hasAny([
                    'current_year_level',
                    'current_semester',
                    'academic_standing',
                    'status',
                ])) {
                    $validator->errors()->add('record', 'At least one mutable student field is required.');
                }
            },
        ];
    }
}
