<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupVersion;
use App\Models\CalendarEvent;
use App\Models\PersonalFile;
use App\Models\PersonalReport;
use App\Models\User;
use Database\Seeders\AdminSetupsCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AccountModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default setups like titles, lockout policies, etc.
        $this->seed(AdminSetupsCatalogueSeeder::class);
    }

    public function test_user_can_login_with_email_or_username(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@mema.ac.ke',
            'username' => 'testusername',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // 1. Login with Email
        $response = $this->post(route('login.store'), [
            'email' => 'testuser@mema.ac.ke',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'));

        // 2. Login with Username
        $response = $this->post(route('login.store'), [
            'email' => 'testusername',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_account_gets_locked_out_after_max_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'lockout@mema.ac.ke',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $this->withoutExceptionHandling();

        // Attempt failed login 5 times (configured in seeder)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login.store'), [
                'email' => 'lockout@mema.ac.ke',
                'password' => 'wrongpassword',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // Verify account is locked
        $user->refresh();
        $this->assertNotNull($user->locked_until);

        // 6th attempt should return lockout message
        $response = $this->post(route('login.store'), [
            'email' => 'lockout@mema.ac.ke',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('locked', $response->getSession()->get('errors')->first('email'));
    }

    public function test_user_can_view_profile_and_edit_fields(): void
    {
        $user = User::factory()->create([
            'email' => 'editor@mema.ac.ke',
            'password' => Hash::make('password'),
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'title' => 'Mr',
        ]);

        $this->actingAs($user);

        // View profile Overview
        $response = $this->get(route('account.show', 'overview'));
        $response->assertOk();
        $response->assertSee('John Doe');
        $response->assertSee('Mr');

        // Edit profile
        $response = $this->post(route('account.profile.update'), [
            'title' => 'Dr',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'editor@mema.ac.ke', // keep same
            'phone_number' => '+254700000000',
            'gender' => 'M',
            'description' => 'Academic Profile Info',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals('Dr', $user->title);
        $this->assertEquals('John Smith', $user->name);
        $this->assertEquals('+254700000000', $user->phone_number);
    }

    public function test_profile_email_change_requires_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'original@mema.ac.ke',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = $this->post(route('account.profile.update'), [
            'title' => 'Mr',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'newemail@mema.ac.ke',
        ]);

        $response->assertRedirect();
        $user->refresh();

        // Email remains original until verified
        $this->assertEquals('original@mema.ac.ke', $user->email);
        $this->assertEquals('newemail@mema.ac.ke', $user->email_change_pending);
        $this->assertNotNull($user->email_verification_token);

        // Verify pending email
        $response = $this->get(route('account.verify-email', $user->email_verification_token));
        $response->assertRedirect(route('account.show', 'profile'));

        $user->refresh();
        $this->assertEquals('newemail@mema.ac.ke', $user->email);
        $this->assertNull($user->email_change_pending);
        $this->assertNull($user->email_verification_token);
    }

    public function test_user_can_upload_and_delete_avatar(): void
    {
        Storage::fake('documents');

        $user = User::factory()->create([
            'email' => 'avatar@mema.ac.ke',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post(route('account.profile.avatar.upload'), [
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertNotNull($user->profile_photo);
        Storage::disk('documents')->assertExists($user->profile_photo);

        // Delete avatar
        $response = $this->delete(route('account.profile.avatar.delete'));
        $response->assertRedirect();
        $user->refresh();
        $this->assertNull($user->profile_photo);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'email' => 'pw@mema.ac.ke',
            'password' => Hash::make('old_password'),
        ]);

        $this->actingAs($user);

        $response = $this->post(route('account.security.password'), [
            'current_password' => 'old_password',
            'new_password' => 'NewSecurePassword1!',
            'new_password_confirmation' => 'NewSecurePassword1!',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword1!', $user->password));
    }

    public function test_calendar_events_crud_and_google_sync(): void
    {
        $user = User::factory()->create([
            'email' => 'cal@mema.ac.ke',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        // Create
        $response = $this->post(route('account.calendar.events.store'), [
            'title' => 'Staff Meeting',
            'description' => 'Discuss syllabus updates',
            'start_time' => now()->addHour()->toDateTimeString(),
            'end_time' => now()->addHours(2)->toDateTimeString(),
            'category' => 'Meeting',
            'reminder_minutes' => 15,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('calendar_events', ['title' => 'Staff Meeting', 'user_id' => $user->id]);

        $event = CalendarEvent::first();

        // Update
        $response = $this->put(route('account.calendar.events.update', $event->id), [
            'title' => 'Rescheduled Meeting',
            'description' => 'Discuss syllabus updates',
            'start_time' => now()->addHour()->toDateTimeString(),
            'end_time' => now()->addHours(2)->toDateTimeString(),
            'category' => 'Meeting',
            'reminder_minutes' => 15,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('calendar_events', ['title' => 'Rescheduled Meeting', 'id' => $event->id]);

        // Connect Google Calendar
        $response = $this->post(route('account.calendar.google.connect'));
        $response->assertRedirect();
        $this->assertDatabaseHas('user_calendar_connections', ['user_id' => $user->id]);

        // Sync Google Calendar
        $response = $this->post(route('account.calendar.google.sync'));
        $response->assertRedirect();

        // Delete
        $response = $this->delete(route('account.calendar.events.delete', $event->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    public function test_private_files_manager_crud_and_quota_limits(): void
    {
        Storage::fake('documents');

        $user = User::factory()->create([
            'email' => 'files@mema.ac.ke',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        // 1. Create Folder
        $response = $this->post(route('account.files.folder'), [
            'folder_name' => 'Documents Folder',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('personal_files', ['name' => 'Documents Folder', 'is_folder' => true]);

        $folder = PersonalFile::where('is_folder', true)->first();

        // 2. Upload File (within limits)
        $file = UploadedFile::fake()->createWithContent('assignment.pdf', "%PDF-1.4\n".str_repeat('A', 500 * 1024)); // 500KB
        $response = $this->post(route('account.files.upload'), [
            'file' => $file,
            'folder_id' => $folder->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('personal_files', ['name' => 'assignment.pdf', 'is_folder' => false, 'parent_id' => $folder->id]);

        $personalFile = PersonalFile::where('is_folder', false)->first();

        // 3. Rename File
        $response = $this->post(route('account.files.rename', $personalFile->id), [
            'name' => 'verified_assignment.pdf',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('personal_files', ['name' => 'verified_assignment.pdf', 'id' => $personalFile->id]);

        // 4. Soft Delete File
        $response = $this->delete(route('account.files.delete', $personalFile->id));
        $response->assertRedirect();
        $this->assertSoftDeleted('personal_files', ['id' => $personalFile->id]);

        // 5. Restore File
        $response = $this->post(route('account.files.restore', $personalFile->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('personal_files', ['id' => $personalFile->id, 'deleted_at' => null]);

        // 6. Quota Enforcement Test (Simulate Quota Limit)
        // Override quota to 0 MB to reject any uploads
        $definition = AdminSetupDefinition::where('setup_key', 'files.storage_quotas')->first();
        AdminSetupVersion::where('admin_setup_definition_id', $definition->id)->delete();
        AdminSetupVersion::create([
            'id' => (string) Str::uuid(),
            'admin_setup_definition_id' => $definition->id,
            'version' => 2,
            'status' => 'ACTIVE',
            'configuration' => ['quota_mb' => 0],
            'effective_from' => '2026-01-01',
            'checksum' => 'checksum',
            'change_reason' => 'Test quota lock',
        ]);

        $largeFile = UploadedFile::fake()->createWithContent('rejected_file.pdf', "%PDF-1.4\n".str_repeat('A', 1000));
        $response = $this->post(route('account.files.upload'), [
            'file' => $largeFile,
        ]);
        $response->assertRedirect();
        // Quota meter fails and returns error in session
        $response->assertSessionHas('error');
    }

    public function test_reports_crud_permissions_and_duplication(): void
    {
        $user = User::factory()->create([
            'email' => 'reporter@mema.ac.ke',
            'password' => Hash::make('password'),
            'role' => 'admin', // Admins have access to all sources
        ]);

        $this->actingAs($user);

        // Create Report
        $response = $this->post(route('account.reports.store'), [
            'name' => 'Active Courses Report',
            'description' => 'List all registered courses',
            'source' => 'courses',
            'columns' => ['id', 'name', 'code'],
            'tags' => ['Academic', 'Registration'],
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('personal_reports', ['name' => 'Active Courses Report', 'user_id' => $user->id]);

        $report = PersonalReport::first();

        // Duplicate Report
        $response = $this->post(route('account.reports.duplicate', $report->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('personal_reports', ['name' => 'Copy of Active Courses Report', 'user_id' => $user->id]);

        // Run Report
        $response = $this->get(route('account.reports.run', $report->id));
        $response->assertOk();
        $response->assertJsonStructure(['reportName', 'source', 'columns', 'data']);
    }

    public function test_user_can_save_preferences_tabs(): void
    {
        $user = User::factory()->create([
            'email' => 'prefs@mema.ac.ke',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = $this->put(route('account.preferences'), [
            'language' => 'sw',
            'timezone' => 'Africa/Nairobi',
            'theme' => 'dark',
            'accessibility_reduced_motion' => '1',
            'accessibility_high_contrast' => '0',
            'privacy_discoverable' => '1',
            'comm_email' => '1',
            'comm_sms' => '0',
            'comm_digest' => 'daily',
            'learn_forum' => 'digest',
            'learn_editor' => 'rich',
            'learn_calendar' => 'month',
            'learn_content_bank' => 'internal',
            'learn_file' => 'grid',
        ]);

        $response->assertRedirect();
        $user->load('preference');
        $this->assertEquals('sw', $user->preference->language);
        $this->assertEquals('dark', $user->preference->theme);
        $this->assertTrue($user->preference->accessibility_settings['reduced_motion']);
        $this->assertEquals('daily', $user->preference->communication_settings['digest']);
    }
}
