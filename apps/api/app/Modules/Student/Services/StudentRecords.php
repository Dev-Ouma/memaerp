<?php

declare(strict_types=1);

namespace App\Modules\Student\Services;

use App\Modules\Iam\Models\User;
use App\Modules\Iam\Services\AccessControl;
use App\Modules\Student\Models\Student;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final readonly class StudentRecords
{
    public function __construct(private AccessControl $access) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return CursorPaginator<int, Student>
     */
    public function paginate(User $actor, array $filters, string $sort, int $perPage): CursorPaginator
    {
        $query = $this->baseQuery();
        $this->access->scopeQuery($query, $actor, 'student.record.view');

        foreach (['status', 'academic_standing', 'programme_id', 'campus_id', 'department_id'] as $filter) {
            if (isset($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        if (isset($filters['search'])) {
            $term = trim((string) $filters['search']);
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);

            $query->where(function (Builder $search) use ($escaped): void {
                $search->where('student_number', 'ilike', "%{$escaped}%")
                    ->orWhereHas('person', function (Builder $person) use ($escaped): void {
                        $person->where('given_name', 'ilike', "%{$escaped}%")
                            ->orWhere('middle_name', 'ilike', "%{$escaped}%")
                            ->orWhere('family_name', 'ilike', "%{$escaped}%")
                            ->orWhere('primary_email', 'ilike', "%{$escaped}%");
                    });
            });
        }

        [$column, $direction] = str_starts_with($sort, '-')
            ? [substr($sort, 1), 'desc']
            : [$sort, 'asc'];

        return $query
            ->orderBy($column, $direction)
            ->orderBy('id')
            ->cursorPaginate($perPage);
    }

    public function findVisible(User $actor, string $id, string $permission = 'student.record.view'): Student
    {
        $query = $this->baseQuery()->whereKey($id);
        $this->access->scopeQuery($query, $actor, $permission);

        $student = $query->first();

        if ($student === null) {
            throw (new ModelNotFoundException)->setModel(Student::class, [$id]);
        }

        return $student;
    }

    public function findVisibleByNumber(User $actor, string $studentNumber, string $permission = 'student.record.view'): Student
    {
        $query = $this->baseQuery()->where('student_number', $studentNumber);
        $this->access->scopeQuery($query, $actor, $permission);

        $student = $query->first();

        if ($student === null) {
            throw (new ModelNotFoundException)->setModel(Student::class, [$studentNumber]);
        }

        return $student;
    }

    /** @param array<string, mixed> $attributes */
    public function update(User $actor, string $id, array $attributes, string $reason): Student
    {
        return DB::transaction(function () use ($actor, $id, $attributes, $reason): Student {
            $student = $this->findVisible($actor, $id, 'student.record.update');

            $student->auditReason($reason)->fill($attributes)->save();

            return $student->refresh()->load([
                'person',
                'programme.department',
                'campus',
                'admissionYear',
            ]);
        });
    }

    /** @return Builder<Student> */
    private function baseQuery(): Builder
    {
        return Student::query()->with([
            'person',
            'programme.department',
            'campus',
            'admissionYear',
        ]);
    }
}
