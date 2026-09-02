<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Platform\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

#[Fillable(['name', 'email', 'password', 'role', 'first_name', 'last_name', 'gender', 'address', 'profile_photo', 'is_active', 'legacy_id', 'title', 'username', 'phone_number', 'department', 'first_login_at', 'last_successful_login_at', 'recovery_email', 'email_verification_token', 'email_change_pending', 'description'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            if (Schema::hasTable('user_stakeholder_types')) {
                $role = $user->role ?: 'student';
                $user->stakeholderTypes()->firstOrCreate(['stakeholder_type' => $role], ['is_active' => true]);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'first_login_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_successful_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function stakeholderTypes(): HasMany
    {
        return $this->hasMany(UserStakeholderType::class);
    }

    public function rbacAssignments(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function applicantProfile(): HasOne
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function calendarConnection(): HasOne
    {
        return $this->hasOne(UserCalendarConnection::class);
    }

    public function personalFiles(): HasMany
    {
        return $this->hasMany(PersonalFile::class);
    }

    public function personalFileLogs(): HasMany
    {
        return $this->hasMany(PersonalFileLog::class);
    }

    public function personalReports(): HasMany
    {
        return $this->hasMany(PersonalReport::class);
    }

    public function trustedDevices(): HasMany
    {
        return $this->hasMany(UserTrustedDevice::class);
    }

    public function securityKeys(): HasMany
    {
        return $this->hasMany(SecurityKey::class);
    }

    public function loginActivities(): HasMany
    {
        return $this->hasMany(LoginActivity::class);
    }

    public function activeRole(): string
    {
        $active = session('active_stakeholder_type');

        return (is_string($active) && $this->stakeholderTypes()->where('stakeholder_type', $active)->where('is_active', true)->exists() ? $active : $this->role) ?: 'student';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student', 'guardian_id', 'student_id')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    public function roleLabel(): string
    {
        return match ($this->activeRole()) {
            'staff' => 'Teacher',
            'admin' => 'College admin',
            default => ucfirst($this->role),
        };
    }
}
