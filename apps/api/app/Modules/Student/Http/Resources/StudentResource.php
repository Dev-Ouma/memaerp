<?php

declare(strict_types=1);

namespace App\Modules\Student\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StudentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'student_number' => $this->resource->student_number,
            'status' => $this->resource->status,
            'academic_standing' => $this->resource->academic_standing,
            'current_year_level' => $this->resource->current_year_level,
            'current_semester' => $this->resource->current_semester,
            'matriculated_on' => $this->resource->matriculated_on?->toDateString(),
            'person' => [
                'id' => $this->resource->person->id,
                'given_name' => $this->resource->person->given_name,
                'middle_name' => $this->resource->person->middle_name,
                'family_name' => $this->resource->person->family_name,
                'full_name' => $this->resource->person->full_name,
                'primary_email' => $this->resource->person->primary_email,
                'primary_phone' => $this->resource->person->primary_phone,
            ],
            'programme' => [
                'id' => $this->resource->programme->id,
                'code' => $this->resource->programme->code,
                'name' => $this->resource->programme->name,
            ],
            'campus' => [
                'id' => $this->resource->campus->id,
                'code' => $this->resource->campus->code,
                'name' => $this->resource->campus->name,
            ],
            'department' => $this->whenLoaded('programme', fn (): array => [
                'id' => $this->resource->programme->department->id,
                'code' => $this->resource->programme->department->code,
                'name' => $this->resource->programme->department->name,
            ]),
            'admission_year' => [
                'id' => $this->resource->admissionYear->id,
                'code' => $this->resource->admissionYear->code,
                'name' => $this->resource->admissionYear->name,
            ],
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
