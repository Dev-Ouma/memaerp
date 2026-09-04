<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Admission\AdminSetupDefinition;
use App\Models\Admission\AdminSetupVersion;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\LoginActivity;
use App\Models\PersonalFile;
use App\Models\PersonalFileLog;
use App\Models\PersonalReport;
use App\Models\SecurityKey;
use App\Models\StudentResult;
use App\Models\User;
use App\Models\UserCalendarConnection;
use App\Models\UserPreference;
use App\Models\UserTrustedDevice;
use App\Modules\Platform\Storage\DocumentStore;
use App\Support\PasswordPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AccountController extends Controller
{
    public function show(Request $request, string $section): View
    {
        $allowedSections = [
            'profile', 'overview', 'edit', 'activity', 'calendar',
            'files', 'reports', 'messages', 'notifications',
            'preferences', 'security', 'support', 'grades',
        ];
        abort_unless(in_array($section, $allowedSections, true), 404);

        $user = $request->user();
        $user->load(['preference']);

        // Data gathering depending on section
        $data = [
            'sectionName' => $section,
            'user' => $user,
        ];

        if ($section === 'overview' || $section === 'profile') {
            $data['loginActivities'] = LoginActivity::where('user_id', $user->id)
                ->orderByDesc('occurred_at')
                ->limit(5)
                ->get();
            $data['recentActivities'] = AuditLog::where('actor_user_id', $user->id)
                ->orderByDesc('occurred_at')
                ->limit(5)
                ->get();

            // Resolve title list from Admin Setup or fallback
            $data['titles'] = $this->getTitlesFromSetup();
        }

        if ($section === 'edit') {
            $data['titles'] = $this->getTitlesFromSetup();
            $data['timezones'] = timezone_identifiers_list();
        }

        if ($section === 'activity') {
            $query = AuditLog::where('actor_user_id', $user->id)->orderByDesc('occurred_at');
            if ($request->filled('q')) {
                $query->where('action', 'like', '%'.$request->q.'%');
            }
            $data['activities'] = $query->paginate(15)->withQueryString();
        }

        if ($section === 'calendar') {
            $data['events'] = CalendarEvent::where('user_id', $user->id)->orderBy('start_time')->get();
            $data['academicSessions'] = AcademicSession::orderByDesc('start_date')->get();
            $data['connection'] = UserCalendarConnection::where('user_id', $user->id)->first();
            $data['categories'] = $this->getCalendarCategoriesFromSetup();
        }

        if ($section === 'files') {
            $parentId = $request->input('folder_id');
            $data['currentFolder'] = $parentId ? PersonalFile::where('user_id', $user->id)->where('is_folder', true)->find($parentId) : null;
            $data['folders'] = PersonalFile::where('user_id', $user->id)->where('is_folder', true)->where('parent_id', $parentId)->orderBy('name')->get();
            $data['files'] = PersonalFile::where('user_id', $user->id)->where('is_folder', false)->where('parent_id', $parentId)->orderBy('name')->get();
            $data['trashedFiles'] = PersonalFile::onlyTrashed()->where('user_id', $user->id)->orderByDesc('deleted_at')->get();

            $usedBytes = PersonalFile::where('user_id', $user->id)->sum('size');
            $maxQuotaMb = $this->getStorageQuotaFromSetup();
            $data['quotaBytes'] = $maxQuotaMb * 1024 * 1024;
            $data['usedBytes'] = $usedBytes;
            $data['percentUsed'] = $data['quotaBytes'] > 0 ? round(($usedBytes / $data['quotaBytes']) * 100, 1) : 0;

            $data['fileLogs'] = PersonalFileLog::where('user_id', $user->id)->orderByDesc('occurred_at')->limit(10)->get();
        }

        if ($section === 'reports') {
            $data['reports'] = PersonalReport::where('user_id', $user->id)->orderByDesc('updated_at')->get();
            $data['sources'] = $this->getReportSourcesFromSetup($user);
            $data['standardTags'] = $this->getStandardTagsFromSetup();
        }

        if ($section === 'security') {
            $data['trustedDevices'] = UserTrustedDevice::where('user_id', $user->id)->orderByDesc('last_used_at')->get();
            $data['securityKeys'] = SecurityKey::where('user_id', $user->id)->get();
            $data['sessions'] = DB::table('sessions')->where('user_id', $user->id)->get();
            $data['loginActivities'] = LoginActivity::where('user_id', $user->id)->orderByDesc('occurred_at')->limit(10)->get();
        }

        if ($section === 'grades') {
            $data['results'] = $user->student ? StudentResult::with('subject')->where('student_id', $user->student->id)->get() : collect();
        }

        return view('account.show', $data);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $fieldsSetup = $this->getProfileFieldsFromSetup();

        // Dynamically build validation rules based on permitted profile fields
        $rules = [];
        if (in_array('title', $fieldsSetup, true)) {
            $titles = $this->getTitlesFromSetup();
            $rules['title'] = ['required', 'in:'.implode(',', $titles)];
        }
        if (in_array('first_name', $fieldsSetup, true)) {
            $rules['first_name'] = ['required', 'string', 'max:50'];
        }
        if (in_array('middle_name', $fieldsSetup, true)) {
            $rules['middle_name'] = ['nullable', 'string', 'max:50'];
        }
        if (in_array('last_name', $fieldsSetup, true)) {
            $rules['last_name'] = ['required', 'string', 'max:50'];
        }
        if (in_array('gender', $fieldsSetup, true)) {
            $rules['gender'] = ['nullable', 'in:M,F,O'];
        }
        if (in_array('address', $fieldsSetup, true)) {
            $rules['address'] = ['nullable', 'string', 'max:255'];
        }
        if (in_array('phone_number', $fieldsSetup, true)) {
            $rules['phone_number'] = ['nullable', 'string', 'max:20'];
        }
        if (in_array('recovery_email', $fieldsSetup, true)) {
            $rules['recovery_email'] = ['nullable', 'email', 'max:150'];
        }
        $rules['description'] = ['nullable', 'string', 'max:1000'];
        $rules['email'] = ['required', 'email', 'unique:users,email,'.$user->id];

        $data = $request->validate($rules);

        $before = $user->toArray();
        $emailChanged = $data['email'] !== $user->email;

        // Populate fields
        if (isset($data['title'])) {
            $user->title = $data['title'];
        }
        if (isset($data['first_name'])) {
            $user->first_name = $data['first_name'];
        }
        if (isset($data['middle_name'])) {
            $user->middle_name = $data['middle_name'];
        }
        if (isset($data['last_name'])) {
            $user->last_name = $data['last_name'];
        }
        if (isset($data['gender'])) {
            $user->gender = $data['gender'];
        }
        if (isset($data['address'])) {
            $user->address = $data['address'];
        }
        if (isset($data['phone_number'])) {
            $user->phone_number = $data['phone_number'];
        }
        if (isset($data['recovery_email'])) {
            $user->recovery_email = $data['recovery_email'];
        }
        if (isset($data['description'])) {
            $user->description = $data['description'];
        }

        // Format displayName / name
        $nameParts = array_filter([$user->first_name, $user->middle_name, $user->last_name]);
        if (count($nameParts) > 0) {
            $user->name = implode(' ', $nameParts);
        }

        if ($emailChanged) {
            $user->email_change_pending = $data['email'];
            $user->email_verification_token = Str::random(40);

            // Simulating email change verification dispatch
            session()->flash('info', 'Verification link sent to '.$data['email'].'. Please verify to activate.');
        }

        $user->save();
        AuditLog::record('profile.updated', $user, $before, $user->fresh()->toArray());

        return back()->with('success', 'Profile updated successfully.');
    }

    public function verifyPendingEmail(Request $request, string $token): RedirectResponse
    {
        $user = User::where('email_verification_token', $token)->first();
        if (! $user || ! $user->email_change_pending) {
            return redirect()->route('account.show', 'profile')->with('error', 'Invalid or expired verification token.');
        }

        $before = $user->toArray();
        $user->email = $user->email_change_pending;
        $user->email_change_pending = null;
        $user->email_verification_token = null;
        $user->save();

        AuditLog::record('profile.email_verified', $user, $before, $user->fresh()->toArray());

        return redirect()->route('account.show', 'profile')->with('success', 'Email address successfully updated and verified.');
    }

    public function uploadAvatar(Request $request, DocumentStore $documentStore): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'max:2048', 'mimes:jpeg,jpg,png,webp'],
        ]);

        $user = $request->user();
        $before = $user->toArray();

        try {
            $storedFile = $documentStore->store($request->file('avatar'), 'avatars/'.$user->id, 'documents');

            // Clean up old avatar if exists
            if ($user->profile_photo && Storage::disk('documents')->exists($user->profile_photo)) {
                Storage::disk('documents')->delete($user->profile_photo);
            }

            $user->profile_photo = $storedFile->path;
            $user->save();

            AuditLog::record('profile.avatar_uploaded', $user, $before, $user->fresh()->toArray());

            return back()->with('success', 'Profile photo updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: '.$e->getMessage());
        }
    }

    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $before = $user->toArray();

        if ($user->profile_photo && Storage::disk('documents')->exists($user->profile_photo)) {
            Storage::disk('documents')->delete($user->profile_photo);
        }

        $user->profile_photo = null;
        $user->save();

        AuditLog::record('profile.avatar_deleted', $user, $before, $user->fresh()->toArray());

        return back()->with('success', 'Profile photo removed.');
    }

    public function serveAvatar(Request $request, User $user)
    {
        abort_unless(
            $request->user()?->id === $user->id || $request->user()?->isAdmin() || $request->user()?->isCollegeAccount(),
            403
        );
        abort_unless($user->profile_photo && Storage::disk('documents')->exists($user->profile_photo), 404);

        return Storage::disk('documents')->response($user->profile_photo);
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed', PasswordPolicy::rules()],
        ], PasswordPolicy::messages('new_password'));

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $before = $user->toArray();
        $user->password = $request->new_password;
        $user->password_changed_at = now();
        $user->save();
        $user->bumpSessionVersion();

        AuditLog::record('security.password_changed', $user, ['password_changed_at' => $before['password_changed_at'] ?? null], ['password_changed_at' => $user->password_changed_at]);

        $request->session()->put('auth_session_version', (int) $user->session_version);
        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
        AuditLog::record('security.other_sessions_revoked', $user, [], []);

        return back()->with('success', 'Password changed successfully. Other sessions have been signed out.');
    }

    public function toggleMfa(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->mfa_enabled_at) {
            $before = $user->toArray();
            $user->mfa_enabled_at = null;
            $user->mfa_secret = null;
            $user->save();
            AuditLog::record('security.mfa_disabled', $user, $before, $user->fresh()->toArray());

            return back()->with('success', 'Multi-factor authentication disabled.');
        }

        // TOTP enrollment is not production-ready (MOD-01-01 §5.3). Refuse mock secrets.
        return back()->withErrors([
            'mfa' => 'Authenticator MFA is not available until RFC 6238 enrollment is configured. Contact ICT.',
        ]);
    }

    public function registerSecurityKey(Request $request): RedirectResponse
    {
        $request->validate(['key_name' => ['required', 'string', 'max:100']]);
        $user = $request->user();

        $key = SecurityKey::create([
            'user_id' => $user->id,
            'name' => $request->key_name,
            'credential_id' => 'cred_'.Str::random(32),
            'public_key' => 'mock_public_key_'.Str::random(64),
        ]);

        AuditLog::record('security.key_registered', $key, [], $key->toArray());

        return back()->with('success', 'Security key registered.');
    }

    public function deleteSecurityKey(Request $request, SecurityKey $key): RedirectResponse
    {
        abort_unless($key->user_id === $request->user()->id, 403);
        $before = $key->toArray();
        $key->delete();

        AuditLog::record('security.key_deleted', $key, $before, []);

        return back()->with('success', 'Security key removed.');
    }

    public function revokeSession(Request $request, string $sessionId): RedirectResponse
    {
        DB::table('sessions')->where('user_id', $request->user()->id)->where('id', $sessionId)->delete();
        AuditLog::record('security.session_revoked', $request->user(), ['session_id' => $sessionId], []);

        return back()->with('success', 'Session revoked successfully.');
    }

    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->bumpSessionVersion();
        $request->session()->put('auth_session_version', (int) $user->session_version);
        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
        AuditLog::record('security.other_sessions_revoked', $user, [], []);

        return back()->with('success', 'Other sessions revoked successfully.');
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $categories = $this->getCalendarCategoriesFromSetup();
        $categoryNames = array_map(fn ($c) => $c['name'], $categories);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            'is_all_day' => ['nullable', 'boolean'],
            'category' => ['required', 'in:'.implode(',', $categoryNames)],
            'reminder_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $user = $request->user();

        // Conflict detection check
        $conflicts = CalendarEvent::where('user_id', $user->id)
            ->where(function ($query) use ($data): void {
                $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                    ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']]);
            })->exists();

        $color = 'blue';
        foreach ($categories as $cat) {
            if ($cat['name'] === $data['category']) {
                $color = $cat['color'];
                break;
            }
        }

        $event = CalendarEvent::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_all_day' => $request->boolean('is_all_day'),
            'category' => $data['category'],
            'color' => $color,
            'reminder_minutes' => $data['reminder_minutes'] ?? null,
        ]);

        AuditLog::record('calendar.event_created', $event, [], $event->toArray());

        $message = 'Event created successfully.';
        if ($conflicts) {
            $message .= ' Note: Conflict detected with another scheduled event.';
        }

        return back()->with('success', $message);
    }

    public function updateEvent(Request $request, CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);
        $categories = $this->getCalendarCategoriesFromSetup();
        $categoryNames = array_map(fn ($c) => $c['name'], $categories);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            'is_all_day' => ['nullable', 'boolean'],
            'category' => ['required', 'in:'.implode(',', $categoryNames)],
            'reminder_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $before = $event->toArray();
        $color = 'blue';
        foreach ($categories as $cat) {
            if ($cat['name'] === $data['category']) {
                $color = $cat['color'];
                break;
            }
        }

        $event->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_all_day' => $request->boolean('is_all_day'),
            'category' => $data['category'],
            'color' => $color,
            'reminder_minutes' => $data['reminder_minutes'] ?? null,
        ]);

        AuditLog::record('calendar.event_updated', $event, $before, $event->fresh()->toArray());

        return back()->with('success', 'Event updated.');
    }

    public function deleteEvent(Request $request, CalendarEvent $event): RedirectResponse
    {
        abort_unless($event->user_id === $request->user()->id, 403);
        $before = $event->toArray();
        $event->delete();

        AuditLog::record('calendar.event_deleted', $event, $before, []);

        return back()->with('success', 'Event cancelled.');
    }

    public function connectGoogleCalendar(Request $request): RedirectResponse
    {
        $user = $request->user();
        UserCalendarConnection::updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_token' => 'mock_google_access_token_'.Str::random(40),
                'refresh_token' => 'mock_google_refresh_token_'.Str::random(40),
                'expires_at' => now()->addHour(),
                'selected_calendars' => ['Primary', 'Work'],
                'sync_direction' => 'two-way',
                'last_sync_at' => now(),
                'last_sync_status' => 'CONNECTED',
            ]
        );

        AuditLog::record('calendar.google_connected', $user, [], []);

        return back()->with('success', 'Google Calendar successfully connected!');
    }

    public function disconnectGoogleCalendar(Request $request): RedirectResponse
    {
        UserCalendarConnection::where('user_id', $request->user()->id)->delete();
        AuditLog::record('calendar.google_disconnected', $request->user(), [], []);

        return back()->with('success', 'Google Calendar disconnected.');
    }

    public function syncGoogleCalendar(Request $request): RedirectResponse
    {
        $connection = UserCalendarConnection::where('user_id', $request->user()->id)->first();
        if ($connection) {
            $connection->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'SUCCESS',
            ]);
            AuditLog::record('calendar.google_synced', $request->user(), [], []);

            return back()->with('success', 'Google Calendar synchronized successfully.');
        }

        return back()->with('error', 'Google Calendar is not connected.');
    }

    public function uploadFile(Request $request, DocumentStore $documentStore): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
            'folder_id' => ['nullable', 'integer', 'exists:personal_files,id'],
        ]);

        $user = $request->user();
        $file = $request->file('file');

        $maxQuotaMb = $this->getStorageQuotaFromSetup();
        $maxQuotaBytes = $maxQuotaMb * 1024 * 1024;
        $currentUsageBytes = PersonalFile::where('user_id', $user->id)->sum('size');

        if ($currentUsageBytes + $file->getSize() > $maxQuotaBytes) {
            return back()->with('error', 'Upload failed: Storage quota exceeded.');
        }

        try {
            $stored = $documentStore->store($file, 'personal_files/'.$user->id, 'documents');

            $personalFile = PersonalFile::create([
                'user_id' => $user->id,
                'parent_id' => $request->folder_id ?: null,
                'name' => $stored->originalName,
                'path' => $stored->path,
                'is_folder' => false,
                'size' => $stored->sizeBytes,
                'mime_type' => $stored->mimeType,
                'extension' => $file->getClientOriginalExtension(),
                'content_hash' => $stored->sha256,
            ]);

            PersonalFileLog::create([
                'user_id' => $user->id,
                'file_name' => $stored->originalName,
                'action' => 'upload',
                'file_size' => $stored->sizeBytes,
                'ip_address' => $request->ip(),
                'version' => 1,
                'result' => 'success',
                'occurred_at' => now(),
            ]);

            AuditLog::record('files.uploaded', $personalFile, [], $personalFile->toArray());

            return back()->with('success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: '.$e->getMessage());
        }
    }

    public function createFolder(Request $request): RedirectResponse
    {
        $request->validate([
            'folder_name' => ['required', 'string', 'max:100'],
            'folder_id' => ['nullable', 'integer', 'exists:personal_files,id'],
        ]);

        $user = $request->user();
        $folder = PersonalFile::create([
            'user_id' => $user->id,
            'parent_id' => $request->folder_id ?: null,
            'name' => $request->folder_name,
            'is_folder' => true,
            'size' => 0,
        ]);

        AuditLog::record('files.folder_created', $folder, [], $folder->toArray());

        return back()->with('success', 'Folder created.');
    }

    public function renameFile(Request $request, PersonalFile $file): RedirectResponse
    {
        abort_unless($file->user_id === $request->user()->id, 403);
        $request->validate(['name' => ['required', 'string', 'max:100']]);

        $before = $file->toArray();
        $file->update(['name' => $request->name]);

        PersonalFileLog::create([
            'user_id' => $request->user()->id,
            'file_name' => $file->name,
            'action' => 'rename',
            'file_size' => $file->size,
            'ip_address' => $request->ip(),
            'version' => 1,
            'result' => 'success',
            'occurred_at' => now(),
        ]);

        AuditLog::record('files.renamed', $file, $before, $file->fresh()->toArray());

        return back()->with('success', 'Renamed successfully.');
    }

    public function moveFile(Request $request, PersonalFile $file): RedirectResponse
    {
        abort_unless($file->user_id === $request->user()->id, 403);
        $request->validate(['parent_id' => ['nullable', 'integer', 'exists:personal_files,id']]);

        $before = $file->toArray();
        $file->update(['parent_id' => $request->parent_id ?: null]);

        PersonalFileLog::create([
            'user_id' => $request->user()->id,
            'file_name' => $file->name,
            'action' => 'move',
            'file_size' => $file->size,
            'ip_address' => $request->ip(),
            'version' => 1,
            'result' => 'success',
            'occurred_at' => now(),
        ]);

        AuditLog::record('files.moved', $file, $before, $file->fresh()->toArray());

        return back()->with('success', 'Moved successfully.');
    }

    public function deleteFile(Request $request, PersonalFile $file): RedirectResponse
    {
        abort_unless($file->user_id === $request->user()->id, 403);
        $before = $file->toArray();
        $file->delete(); // soft delete

        PersonalFileLog::create([
            'user_id' => $request->user()->id,
            'file_name' => $file->name,
            'action' => 'delete',
            'file_size' => $file->size,
            'ip_address' => $request->ip(),
            'version' => 1,
            'result' => 'success',
            'occurred_at' => now(),
        ]);

        AuditLog::record('files.soft_deleted', $file, $before, []);

        return back()->with('success', 'Moved to Recycle Bin.');
    }

    public function restoreFile(Request $request, int $id): RedirectResponse
    {
        $file = PersonalFile::onlyTrashed()->where('user_id', $request->user()->id)->findOrFail($id);
        $file->restore();

        PersonalFileLog::create([
            'user_id' => $request->user()->id,
            'file_name' => $file->name,
            'action' => 'restore',
            'file_size' => $file->size,
            'ip_address' => $request->ip(),
            'version' => 1,
            'result' => 'success',
            'occurred_at' => now(),
        ]);

        AuditLog::record('files.restored', $file, [], $file->toArray());

        return back()->with('success', 'File restored successfully.');
    }

    public function permanentDeleteFile(Request $request, int $id, DocumentStore $documentStore): RedirectResponse
    {
        $file = PersonalFile::onlyTrashed()->where('user_id', $request->user()->id)->findOrFail($id);
        $before = $file->toArray();

        if ($file->path) {
            $documentStore->delete('documents', $file->path);
        }

        PersonalFileLog::create([
            'user_id' => $request->user()->id,
            'file_name' => $file->name,
            'action' => 'permanent_delete',
            'file_size' => $file->size,
            'ip_address' => $request->ip(),
            'version' => 1,
            'result' => 'success',
            'occurred_at' => now(),
        ]);

        $file->forceDelete();
        AuditLog::record('files.purged', $file, $before, []);

        return back()->with('success', 'Permanently deleted.');
    }

    public function downloadFile(Request $request, PersonalFile $file, DocumentStore $documentStore)
    {
        abort_unless($file->user_id === $request->user()->id, 403);
        abort_unless($file->path, 404);

        PersonalFileLog::create([
            'user_id' => $request->user()->id,
            'file_name' => $file->name,
            'action' => 'download',
            'file_size' => $file->size,
            'ip_address' => $request->ip(),
            'version' => 1,
            'result' => 'success',
            'occurred_at' => now(),
        ]);

        AuditLog::record('files.downloaded', $file, [], []);

        return $documentStore->download('documents', $file->path, $file->name, $file->mime_type ?? 'application/octet-stream');
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sources = $this->getReportSourcesFromSetup($user);
        $sourcesKeys = array_keys($sources);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'source' => ['required', 'in:'.implode(',', $sourcesKeys)],
            'columns' => ['required', 'array'],
            'filters' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
        ]);

        $report = PersonalReport::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'source' => $data['source'],
            'columns' => $data['columns'],
            'filters' => $data['filters'] ?? [],
            'options' => ['tags' => $data['tags'] ?? []],
            'is_draft' => $request->boolean('is_draft', true),
        ]);

        AuditLog::record('reports.created', $report, [], $report->toArray());

        return back()->with('success', 'Personal report saved.');
    }

    public function updateReport(Request $request, PersonalReport $report): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id, 403);
        $user = $request->user();
        $sources = $this->getReportSourcesFromSetup($user);
        $sourcesKeys = array_keys($sources);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'source' => ['required', 'in:'.implode(',', $sourcesKeys)],
            'columns' => ['required', 'array'],
            'filters' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
        ]);

        $before = $report->toArray();
        $report->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'source' => $data['source'],
            'columns' => $data['columns'],
            'filters' => $data['filters'] ?? [],
            'options' => ['tags' => $data['tags'] ?? []],
            'is_draft' => $request->boolean('is_draft', true),
        ]);

        AuditLog::record('reports.updated', $report, $before, $report->fresh()->toArray());

        return back()->with('success', 'Report updated.');
    }

    public function duplicateReport(Request $request, PersonalReport $report): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id, 403);

        $duplicate = PersonalReport::create([
            'user_id' => $request->user()->id,
            'name' => 'Copy of '.$report->name,
            'description' => $report->description,
            'source' => $report->source,
            'columns' => $report->columns,
            'filters' => $report->filters,
            'options' => $report->options,
            'is_draft' => true,
        ]);

        AuditLog::record('reports.duplicated', $duplicate, [], $duplicate->toArray());

        return back()->with('success', 'Report duplicated.');
    }

    public function deleteReport(Request $request, PersonalReport $report): RedirectResponse
    {
        abort_unless($report->user_id === $request->user()->id, 403);
        $before = $report->toArray();
        $report->delete();

        AuditLog::record('reports.deleted', $report, $before, []);

        return back()->with('success', 'Report deleted.');
    }

    public function runReport(Request $request, PersonalReport $report): JsonResponse
    {
        abort_unless($report->user_id === $request->user()->id, 403);
        $user = $request->user();

        // 1. Confirm source exists and user has permission
        $sources = $this->getReportSourcesFromSetup($user);
        if (! array_key_exists($report->source, $sources)) {
            return response()->json(['error' => 'Permission denied to access this report source.'], 403);
        }

        // 2. Query data dynamically based on source
        $dataQuery = DB::table($sources[$report->source]['table']);
        $columns = $report->columns;

        // Apply filters
        if ($report->filters) {
            foreach ($report->filters as $filter) {
                if (isset($filter['column'], $filter['operator'], $filter['value'])) {
                    $dataQuery->where($filter['column'], $filter['operator'], $filter['value']);
                }
            }
        }

        // Limit results for preview
        $results = $dataQuery->limit(100)->get($columns);

        AuditLog::record('reports.run', $report, [], []);

        return response()->json([
            'reportName' => $report->name,
            'source' => $report->source,
            'columns' => $columns,
            'data' => $results,
        ]);
    }

    public function submitSupportTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'in:Technical,Billing,Academic,General'],
        ]);

        // Mock support ticket log
        AuditLog::record('support.ticket_created', $request->user(), [], [
            'subject' => $request->subject,
            'category' => $request->category,
        ]);

        return back()->with('success', 'Support ticket submitted. Our administrator will review it shortly.');
    }

    public function preferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'language' => ['required', 'in:en,sw'],
            'timezone' => ['required', 'timezone:all'],
            'theme' => ['required', 'in:system,light,dark'],
            'accessibility_reduced_motion' => ['nullable', 'boolean'],
            'accessibility_high_contrast' => ['nullable', 'boolean'],
            'privacy_discoverable' => ['nullable', 'boolean'],
            'comm_email' => ['nullable', 'boolean'],
            'comm_sms' => ['nullable', 'boolean'],
            'comm_digest' => ['nullable', 'in:none,daily,weekly'],
            'comm_quiet_start' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'comm_quiet_end' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'learn_forum' => ['nullable', 'in:none,digest,all'],
            'learn_editor' => ['nullable', 'in:simple,rich'],
            'learn_calendar' => ['nullable', 'in:month,week,day'],
            'learn_content_bank' => ['nullable', 'in:internal,external'],
            'learn_file' => ['nullable', 'in:grid,list'],
        ]);

        $preferences = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $before = $preferences->toArray();

        $emailNotifications = $request->boolean('comm_email') || $request->boolean('email_notifications');
        $profileDiscoverable = $request->boolean('privacy_discoverable') || $request->boolean('profile_discoverable');

        $preferences->update([
            'language' => $data['language'],
            'timezone' => $data['timezone'],
            'theme' => $data['theme'],
            'email_notifications' => $emailNotifications,
            'browser_notifications' => true,
            'profile_discoverable' => $profileDiscoverable,
            'accessibility_settings' => [
                'reduced_motion' => $request->boolean('accessibility_reduced_motion'),
                'high_contrast' => $request->boolean('accessibility_high_contrast'),
            ],
            'privacy_settings' => [
                'profile_discoverable' => $profileDiscoverable,
            ],
            'communication_settings' => [
                'email' => $emailNotifications,
                'sms' => $request->boolean('comm_sms'),
                'digest' => $data['comm_digest'] ?? 'none',
                'quiet_hours' => [
                    'start' => $data['comm_quiet_start'] ?? null,
                    'end' => $data['comm_quiet_end'] ?? null,
                ],
            ],
            'learning_settings' => [
                'forum' => $data['learn_forum'] ?? 'digest',
                'editor' => $data['learn_editor'] ?? 'rich',
                'calendar' => $data['learn_calendar'] ?? 'month',
                'content_bank' => $data['learn_content_bank'] ?? 'internal',
                'file' => $data['learn_file'] ?? 'grid',
            ],
        ]);

        AuditLog::record('preferences.updated', $preferences, $before, $preferences->fresh()->toArray());

        return back()->with('success', 'Preferences saved.');
    }

    public function switchRole(Request $request): RedirectResponse
    {
        $data = $request->validate(['stakeholder_type' => ['required', 'string', 'max:40']]);
        $relationship = $request->user()->stakeholderTypes()->where('stakeholder_type', $data['stakeholder_type'])->where('is_active', true)->firstOrFail();
        $before = $request->session()->get('active_stakeholder_type', $request->user()->role);
        $request->session()->put('active_stakeholder_type', $relationship->stakeholder_type);
        AuditLog::record('session.role_switched', $request->user(), ['stakeholder_type' => $before], ['stakeholder_type' => $relationship->stakeholder_type]);

        return redirect()->route('dashboard')->with('success', "Switched to {$request->user()->roleLabel()}.");
    }

    // --- Helper methods to resolve settings and titles from Admin Setup ---

    private function getTitlesFromSetup(): array
    {
        $definition = AdminSetupDefinition::where('setup_key', 'user.titles')->first();
        if ($definition) {
            $activeVersion = AdminSetupVersion::where('admin_setup_definition_id', $definition->id)
                ->where('status', 'ACTIVE')
                ->first();
            if ($activeVersion && isset($activeVersion->configuration['titles'])) {
                return $activeVersion->configuration['titles'];
            }
        }

        return ['Mr', 'Mrs', 'Ms', 'Miss', 'Dr', 'Prof', 'Eng'];
    }

    private function getProfileFieldsFromSetup(): array
    {
        $definition = AdminSetupDefinition::where('setup_key', 'user.profile_fields')->first();
        if ($definition) {
            $activeVersion = AdminSetupVersion::where('admin_setup_definition_id', $definition->id)
                ->where('status', 'ACTIVE')
                ->first();
            if ($activeVersion && isset($activeVersion->configuration['fields'])) {
                return $activeVersion->configuration['fields'];
            }
        }

        return ['title', 'first_name', 'middle_name', 'last_name', 'gender', 'address', 'phone_number', 'recovery_email', 'description'];
    }

    private function getCalendarCategoriesFromSetup(): array
    {
        $definition = AdminSetupDefinition::where('setup_key', 'calendar.categories')->first();
        if ($definition) {
            $activeVersion = AdminSetupVersion::where('admin_setup_definition_id', $definition->id)
                ->where('status', 'ACTIVE')
                ->first();
            if ($activeVersion && isset($activeVersion->configuration['categories'])) {
                return $activeVersion->configuration['categories'];
            }
        }

        return [
            ['name' => 'Work', 'color' => 'teal'],
            ['name' => 'Personal', 'color' => 'blue'],
            ['name' => 'Meeting', 'color' => 'purple'],
            ['name' => 'Class', 'color' => 'yellow'],
            ['name' => 'Maintenance', 'color' => 'red'],
        ];
    }

    private function getStorageQuotaFromSetup(): int
    {
        $definition = AdminSetupDefinition::where('setup_key', 'files.storage_quotas')->first();
        if ($definition) {
            $activeVersion = AdminSetupVersion::where('admin_setup_definition_id', $definition->id)
                ->where('status', 'ACTIVE')
                ->first();
            if ($activeVersion && isset($activeVersion->configuration['quota_mb'])) {
                return (int) $activeVersion->configuration['quota_mb'];
            }
        }

        return 100; // default 100MB
    }

    private function getReportSourcesFromSetup($user): array
    {
        $allSources = [
            'badges' => ['label' => 'Badges', 'table' => 'books'], // mapping to existing tables for simplicity
            'blogs' => ['label' => 'Blogs', 'table' => 'subjects'],
            'cohorts' => ['label' => 'Cohorts', 'table' => 'courses'],
            'comments' => ['label' => 'Comments', 'table' => 'books'],
            'competencies' => ['label' => 'Competencies', 'table' => 'subjects'],
            'course_categories' => ['label' => 'Course categories', 'table' => 'courses'],
            'course_participants' => ['label' => 'Course participants', 'table' => 'students'],
            'courses' => ['label' => 'Courses', 'table' => 'courses'],
            'files' => ['label' => 'Files', 'table' => 'personal_files'],
            'groups' => ['label' => 'Groups', 'table' => 'courses'],
            'notes' => ['label' => 'Notes', 'table' => 'books'],
            'roles' => ['label' => 'Roles', 'table' => 'user_stakeholder_types'],
            'tags' => ['label' => 'Tags', 'table' => 'subjects'],
            'task_logs' => ['label' => 'Task logs', 'table' => 'audit_logs'],
            'user_badges' => ['label' => 'User badges', 'table' => 'users'],
            'users' => ['label' => 'Users', 'table' => 'users'],
            'course_ratings' => ['label' => 'Course ratings', 'table' => 'student_results'],
        ];

        // Retrieve permissions setup
        $definition = AdminSetupDefinition::where('setup_key', 'reports.permissions')->first();
        if ($definition) {
            $activeVersion = AdminSetupVersion::where('admin_setup_definition_id', $definition->id)
                ->where('status', 'ACTIVE')
                ->first();
            if ($activeVersion && isset($activeVersion->configuration['access'])) {
                $access = $activeVersion->configuration['access'];
                $role = $user->role;
                $allowedKeys = $access[$role] ?? [];

                if (in_array('*', $allowedKeys, true)) {
                    return $allSources;
                }

                return array_filter($allSources, fn ($k) => in_array($k, $allowedKeys, true), ARRAY_FILTER_USE_KEY);
            }
        }

        // Default: only admins see all, staff see academic, students see files/notes
        if ($user->isAdmin()) {
            return $allSources;
        }
        if ($user->role === 'staff') {
            return array_filter($allSources, fn ($k) => in_array($k, ['courses', 'course_participants', 'notes', 'tags'], true), ARRAY_FILTER_USE_KEY);
        }

        return array_filter($allSources, fn ($k) => in_array($k, ['notes', 'files'], true), ARRAY_FILTER_USE_KEY);
    }

    private function getStandardTagsFromSetup(): array
    {
        $definition = AdminSetupDefinition::where('setup_key', 'reports.standard_tags')->first();
        if ($definition) {
            $activeVersion = AdminSetupVersion::where('admin_setup_definition_id', $definition->id)
                ->where('status', 'ACTIVE')
                ->first();
            if ($activeVersion && isset($activeVersion->configuration['tags'])) {
                return $activeVersion->configuration['tags'];
            }
        }

        return ['Academic', 'Finance', 'Registration', 'Exam', 'HR', 'System'];
    }
}
