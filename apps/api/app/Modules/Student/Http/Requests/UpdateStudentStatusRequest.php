<?php

declare(strict_types=1);

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class UpdateStudentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('student.record.status');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:ACTIVE,ON_LEAVE,SUSPENDED,GRADUATED,WITHDRAWN'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}
