<?php

declare(strict_types=1);

namespace App\Modules\Portal\Models;

use App\Modules\Institution\Models\Institution;
use App\Modules\Student\Models\Person;
use App\Platform\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StudentPreference extends BaseModel
{
    protected $table = 'student.portal_preferences';

    protected $fillable = [
        'institution_id', 'person_id', 'locale', 'theme', 'email_alerts', 'sms_alerts', 'dashboard_widgets',
    ];

    protected function casts(): array
    {
        return [
            'email_alerts' => 'boolean',
            'sms_alerts' => 'boolean',
            'dashboard_widgets' => 'array',
        ];
    }

    /** @return BelongsTo<Institution, $this> */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /** @return BelongsTo<Person, $this> */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
