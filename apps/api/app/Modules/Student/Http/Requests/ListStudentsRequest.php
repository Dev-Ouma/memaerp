<?php

declare(strict_types=1);

namespace App\Modules\Student\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class ListStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('student.record.view');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cursor' => ['sometimes', 'string', 'max:2048'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['sometimes', 'string', 'in:student_number,-student_number,matriculated_on,-matriculated_on,created_at,-created_at'],
            'filter' => ['sometimes', 'array:status,academic_standing,programme_id,campus_id,department_id,search'],
            'filter.status' => ['sometimes', 'string', 'in:ACTIVE,ON_LEAVE,SUSPENDED,GRADUATED,WITHDRAWN'],
            'filter.academic_standing' => ['sometimes', 'string', 'in:GOOD_STANDING,PROBATION,SUSPENDED,DISCONTINUED'],
            'filter.programme_id' => ['sometimes', 'uuid'],
            'filter.campus_id' => ['sometimes', 'uuid'],
            'filter.department_id' => ['sometimes', 'uuid'],
            'filter.search' => ['sometimes', 'string', 'min:2', 'max:100'],
        ];
    }
}
