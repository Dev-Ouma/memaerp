<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Admission\Setups\SetupCatalogue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AdminSetupsCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $definitions = SetupCatalogue::definitions();

        // 1. Seed any missing setup definitions
        foreach ($definitions as $key => $definition) {
            $exists = DB::table('admin_setup_definitions')->where('setup_key', $key)->exists();
            if (! $exists) {
                DB::table('admin_setup_definitions')->insert([
                    'id' => (string) Str::uuid(),
                    'setup_key' => $key,
                    'category' => $definition['category'],
                    'name' => $definition['name'],
                    'consumer' => $definition['consumer'],
                    'missing_behaviour' => $definition['missing'],
                    'validation_schema' => '{}',
                    'supports_import' => true,
                    'supports_preview' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 2. Define configurations defaults
        $defaults = [
            'user.titles' => [
                'titles' => ['Mr', 'Mrs', 'Ms', 'Miss', 'Dr', 'Prof', 'Eng'],
            ],
            'user.profile_fields' => [
                'fields' => ['title', 'first_name', 'middle_name', 'last_name', 'gender', 'address', 'phone_number', 'recovery_email', 'description'],
            ],
            'security.password_policies' => [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_numeric' => true,
                'require_special' => true,
            ],
            'security.session_policies' => [
                'timeout_minutes' => 30,
                'max_concurrent_sessions' => 3,
            ],
            'security.mfa_methods' => [
                'methods' => ['totp', 'security_key'],
                'required' => false,
            ],
            'user.avatar_defaults' => [
                'colors' => ['#1A778B', '#FF6845', '#D7A84F', '#0B7286', '#E67E22', '#9B59B6', '#1ABC9C'],
            ],
            'files.types_sizes' => [
                'max_file_size_mb' => 100,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'zip', 'doc', 'docx', 'xls', 'xlsx'],
            ],
            'files.storage_quotas' => [
                'quota_mb' => 100,
            ],
            'calendar.categories' => [
                'categories' => [
                    ['name' => 'Work', 'color' => '#1abc9c'],
                    ['name' => 'Personal', 'color' => '#3498db'],
                    ['name' => 'Meeting', 'color' => '#9b59b6'],
                    ['name' => 'Class', 'color' => '#f1c40f'],
                    ['name' => 'Maintenance', 'color' => '#e74c3c'],
                ],
            ],
            'calendar.google_integration' => [
                'enabled' => true,
                'client_id' => 'mock-google-client-id',
                'client_secret' => 'mock-google-client-secret',
            ],
            'security.activity_retention' => [
                'retention_days' => 365,
            ],
            'reports.sources' => [
                'sources' => ['badges', 'blogs', 'cohorts', 'comments', 'competencies', 'course_categories', 'course_participants', 'courses', 'files', 'groups', 'notes', 'roles', 'tags', 'task_logs', 'user_badges', 'users', 'course_ratings'],
            ],
            'reports.permissions' => [
                'access' => [
                    'admin' => ['*'],
                    'staff' => ['courses', 'course_participants', 'notes', 'tags'],
                    'student' => ['notes', 'files'],
                ],
            ],
            'reports.standard_tags' => [
                'tags' => ['Academic', 'Finance', 'Registration', 'Exam', 'HR', 'System'],
            ],
            'notification.channels' => [
                'channels' => ['email', 'sms', 'browser'],
                'default_active' => ['email'],
            ],
            'preferences.defaults' => [
                'language' => 'en',
                'timezone' => 'Africa/Nairobi',
                'theme' => 'system',
                'email_notifications' => true,
                'browser_notifications' => true,
                'profile_discoverable' => false,
            ],
            'security.account_lockout' => [
                'max_failed_attempts' => 5,
                'lockout_duration_minutes' => 15,
            ],
            'security.privacy_retention' => [
                'retention_years' => 7,
                'consent_required' => true,
            ],
        ];

        // 3. Seed versions for the new setup configurations
        foreach ($defaults as $key => $configuration) {
            $definition = DB::table('admin_setup_definitions')->where('setup_key', $key)->first();
            if ($definition) {
                $hasVersion = DB::table('admin_setup_versions')->where('admin_setup_definition_id', $definition->id)->exists();
                if (! $hasVersion) {
                    $json = json_encode($configuration, JSON_THROW_ON_ERROR);
                    DB::table('admin_setup_versions')->insert([
                        'id' => (string) Str::uuid(),
                        'admin_setup_definition_id' => $definition->id,
                        'version' => 1,
                        'status' => 'ACTIVE',
                        'configuration' => $json,
                        'effective_from' => '2026-01-01',
                        'checksum' => hash('sha256', $json),
                        'published_at' => $now,
                        'change_reason' => 'Seeded default setup configurations.',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
