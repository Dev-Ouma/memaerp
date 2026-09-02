@extends('layouts.app')
@section('title', 'My account')
@section('section', 'Account settings')

@section('content')
@php($user = auth()->user())
@php($pref = $user->preference ?? new \App\Models\UserPreference())

<style>
    .account-layout {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 30px;
        align-items: start;
        margin-top: 20px;
    }
    .account-nav {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px 10px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .account-nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        color: #4a5568;
        text-decoration: none;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s ease;
        margin-bottom: 5px;
    }
    .account-nav-item:hover {
        background: #f7fafc;
        color: #1a778b;
    }
    .account-nav-item.active {
        background: #edf8f9;
        color: #1a778b;
        font-weight: 600;
    }
    .account-nav-item svg {
        width: 18px;
        height: 18px;
    }
    .account-content {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        min-height: 500px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .detail-item {
        background: #f8fafc;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #edf2f7;
    }
    .detail-item dt {
        font-size: 12px;
        text-transform: uppercase;
        color: #718096;
        font-weight: 600;
        letter-spacing: 0.05em;
        margin-bottom: 5px;
    }
    .detail-item dd {
        font-size: 15px;
        color: #1a202c;
        font-weight: 500;
        margin: 0;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 14px;
        color: #4a5568;
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        height: 45px;
        padding: 0 15px;
        border: 1px solid #cbd5e0;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        border-color: #1a778b;
        box-shadow: 0 0 0 3px rgba(26,119,139,0.15);
    }
    textarea.form-control {
        height: auto;
        padding: 12px 15px;
    }
    .btn-primary {
        background: linear-gradient(135deg, #1a778b, #0b7286);
        color: #ffffff;
        border: 0;
        border-radius: 6px;
        padding: 12px 24px;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .btn-primary:hover {
        opacity: 0.95;
    }
    .avatar-upload-container {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        background: #f7fafc;
        padding: 20px;
        border-radius: 10px;
        border: 1px dashed #cbd5e0;
    }
    .avatar-preview-wrapper {
        position: relative;
        width: 80px;
        height: 80px;
    }
    .avatar-preview {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        background: #1a778b;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 600;
    }
    .avatar-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .file-tree {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    .file-item {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    .file-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border-color: #cbd5e0;
    }
    .file-item svg {
        width: 40px;
        height: 40px;
        color: #4a5568;
        margin-bottom: 10px;
    }
    .file-item.folder svg {
        color: #d69e2e;
    }
    .file-name {
        font-size: 13px;
        font-weight: 500;
        color: #2d3748;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-actions-menu {
        position: absolute;
        top: 5px;
        right: 5px;
    }
    .file-actions-btn {
        background: transparent;
        border: 0;
        color: #a0aec0;
        cursor: pointer;
        padding: 4px;
        border-radius: 50%;
    }
    .file-actions-btn:hover {
        background: #edf2f7;
        color: #4a5568;
    }
    .custom-calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        margin-top: 15px;
    }
    .calendar-header {
        text-align: center;
        font-weight: 600;
        color: #718096;
        padding: 10px 0;
        font-size: 13px;
        text-transform: uppercase;
    }
    .calendar-day {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        aspect-ratio: 1.2;
        padding: 8px;
        position: relative;
        cursor: pointer;
        border-radius: 4px;
        min-height: 80px;
    }
    .calendar-day:hover {
        background: #f7fafc;
    }
    .calendar-day.today {
        background: #edf8f9;
        border-color: #1a778b;
    }
    .calendar-day-num {
        font-size: 12px;
        font-weight: 600;
        color: #4a5568;
    }
    .calendar-event-pill {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        margin-top: 4px;
        font-weight: 600;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .activity-timeline {
        position: relative;
        padding-left: 20px;
        border-left: 2px solid #e2e8f0;
        margin-top: 20px;
    }
    .activity-item {
        position: relative;
        margin-bottom: 25px;
    }
    .activity-item::before {
        content: "";
        position: absolute;
        left: -27px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #1a778b;
        border: 2px solid #ffffff;
    }
    .activity-time {
        font-size: 12px;
        color: #a0aec0;
        font-weight: 500;
    }
    .activity-desc {
        font-size: 14px;
        font-weight: 500;
        color: #2d3748;
        margin-top: 2px;
    }
    .tab-container {
        display: flex;
        gap: 15px;
        border-bottom: 2px solid #edf2f7;
        margin-bottom: 20px;
    }
    .tab-btn {
        padding: 10px 15px;
        cursor: pointer;
        font-weight: 600;
        color: #718096;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
    }
    .tab-btn.active {
        color: #1a778b;
        border-color: #1a778b;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        place-items: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
    }
    .modal-card {
        background: #ffffff;
        border-radius: 12px;
        width: min(100%, 550px);
        padding: 30px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }
    .modal-close {
        background: transparent;
        border: 0;
        cursor: pointer;
        color: #a0aec0;
    }
    .modal-close:hover {
        color: #4a5568;
    }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">My account settings</div>
        <h1 class="heading">Workspace Dashboard</h1>
        <p class="sub" style="margin:0">Manage your profile, security, configurations, files, calendar, and reports.</p>
    </div>
</div>

@if (session('info'))
    <div class="alert alert-info" style="background:#e8f0fe;color:#1a73e8;border:1px solid #d2e3fc;padding:15px;border-radius:8px;margin-bottom:20px;font-weight:500;">
        {{ session('info') }}
    </div>
@endif

<div class="account-layout">
    <nav class="account-nav">
        @foreach ([
            ['overview', 'user-round', 'Profile Overview'],
            ['edit', 'user-cog', 'Edit Profile'],
            ['activity', 'activity', 'Account Activity'],
            ['calendar', 'calendar-days', 'My Calendar'],
            ['files', 'folder-lock', 'My Files'],
            ['reports', 'file-text', 'My Reports'],
            ['preferences', 'sliders', 'Preferences'],
            ['security', 'shield-check', 'Security Centre'],
            ['support', 'help-circle', 'Help & Support']
        ] as [$sec, $icon, $label])
            <a class="account-nav-item {{ $sectionName === $sec ? 'active' : '' }}" href="{{ route('account.show', $sec) }}">
                <i data-lucide="{{ $icon }}"></i>{{ $label }}
            </a>
        @endforeach
    </nav>

    <div class="account-content">
        {{-- SECTION 1: PROFILE OVERVIEW --}}
        @if ($sectionName === 'overview' || $sectionName === 'profile')
            <div>
                <div class="avatar-upload-container" style="border-style: solid;">
                    <div class="avatar-preview-wrapper">
                        @if ($user->profile_photo)
                            <img class="avatar-preview" src="{{ route('account.profile.avatar.serve', $user->id) }}" alt="Avatar">
                        @else
                            <div class="avatar-preview" style="background:#1a778b;">
                                {{ strtoupper(substr($user->first_name ?: $user->name, 0, 1) . substr($user->last_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h2 style="margin:0;font-size:22px;font-weight:600;">{{ $user->title }} {{ $user->name }}</h2>
                        <p style="margin:4px 0 0;color:#718096;font-weight:500;">{{ $user->email }} • <span class="badge" style="background:#edf8f9;color:#1a778b;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">{{ $user->roleLabel() }}</span></p>
                    </div>
                </div>

                <h3 style="font-size:16px;font-weight:600;margin-top:30px;border-bottom:1px solid #edf2f7;padding-bottom:10px;">Primary Specifications</h3>
                <dl class="detail-grid">
                    <div class="detail-item"><dt>Username</dt><dd>{{ $user->username ?: '—' }}</dd></div>
                    <div class="detail-item"><dt>Phone number</dt><dd>{{ $user->phone_number ?: '—' }}</dd></div>
                    <div class="detail-item"><dt>Department</dt><dd>{{ $user->department ?: 'General Administration' }}</dd></div>
                    <div class="detail-item"><dt>Account status</dt><dd>{{ $user->is_active ? 'Active' : 'Suspended' }}</dd></div>
                    <div class="detail-item"><dt>First Access</dt><dd>{{ $user->first_login_at ? $user->first_login_at->timezone($pref->timezone ?? 'Africa/Nairobi')->format('d M Y, h:i A') : 'Never logged in' }}</dd></div>
                    <div class="detail-item"><dt>Last Access</dt><dd>{{ $user->last_login_at ? $user->last_login_at->timezone($pref->timezone ?? 'Africa/Nairobi')->format('d M Y, h:i A') : 'Never logged in' }}</dd></div>
                    <div class="detail-item"><dt>Last Known IP</dt><dd>{{ $user->last_login_ip ?: '—' }}</dd></div>
                    <div class="detail-item"><dt>MFA Status</dt><dd>{{ $user->mfa_enabled_at ? 'Enabled' : 'Disabled' }}</dd></div>
                </dl>

                <h3 style="font-size:16px;font-weight:600;margin-top:40px;border-bottom:1px solid #edf2f7;padding-bottom:10px;">Recent Activity Timeline</h3>
                <div class="activity-timeline">
                    @forelse($recentActivities as $activity)
                        <div class="activity-item">
                            <span class="activity-time">{{ $activity->occurred_at->timezone($pref->timezone ?? 'Africa/Nairobi')->diffForHumans() }} ({{ $activity->occurred_at->timezone($pref->timezone ?? 'Africa/Nairobi')->format('d M, h:i A') }})</span>
                            <div class="activity-desc">Logged action <strong>{{ $activity->action }}</strong> on module resource.</div>
                        </div>
                    @empty
                        <p style="color:#a0aec0;font-size:14px;margin-top:10px;">No recent activity logs available.</p>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- SECTION 2: EDIT PROFILE --}}
        @if ($sectionName === 'edit')
            <div>
                <form action="{{ route('account.profile.avatar.upload') }}" method="post" enctype="multipart/form-data" class="avatar-upload-container">
                    @csrf
                    <div class="avatar-preview-wrapper">
                        @if ($user->profile_photo)
                            <img class="avatar-preview" src="{{ route('account.profile.avatar.serve', $user->id) }}" alt="Avatar">
                        @else
                            <div class="avatar-preview">
                                {{ strtoupper(substr($user->first_name ?: $user->name, 0, 1) . substr($user->last_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="avatar-actions">
                        <label class="btn-primary" style="padding:8px 16px;font-size:13px;display:inline-block;cursor:pointer;">
                            Upload Photo <input type="file" name="avatar" style="display:none;" onchange="this.form.submit()">
                        </label>
                </form>
                @if ($user->profile_photo)
                    <form action="{{ route('account.profile.avatar.delete') }}" method="post">
                        @csrf @method('delete')
                        <button type="submit" style="background:transparent;border:0;color:#e53e3e;font-size:13px;font-weight:600;cursor:pointer;padding:0;">Remove Photo</button>
                    </form>
                @endif
                    </div>
                </div>

                <form action="{{ route('account.profile.update') }}" method="post">
                    @csrf
                    <div class="grid4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));gap: 20px;">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <select id="title" name="title" class="form-control">
                                @foreach($titles as $t)
                                    <option value="{{ $t }}" @selected($user->title === $t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input id="first_name" type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input id="middle_name" type="text" name="middle_name" class="form-control" value="{{ old('middle_name', $user->middle_name) }}">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
                        </div>
                    </div>

                    <div class="grid4" style="grid-template-columns: 1fr 1fr;gap: 20px;">
                        <div class="form-group">
                            <label for="email">Primary Email</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            @if ($user->email_change_pending)
                                <small style="color:#d69e2e;font-weight:500;">Pending verification to: {{ $user->email_change_pending }}</small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input id="phone_number" type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $user->phone_number) }}">
                        </div>
                    </div>

                    <div class="grid4" style="grid-template-columns: 1fr 1fr;gap: 20px;">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <option value="" @selected(empty($user->gender))>Not specified</option>
                                <option value="M" @selected($user->gender === 'M')>Male</option>
                                <option value="F" @selected($user->gender === 'F')>Female</option>
                                <option value="O" @selected($user->gender === 'O')>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="recovery_email">Recovery Email</label>
                            <input id="recovery_email" type="email" name="recovery_email" class="form-control" value="{{ old('recovery_email', $user->recovery_email) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Postal Address</label>
                        <input id="address" type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                    </div>

                    <div class="form-group">
                        <label for="description">Profile Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $user->description) }}</textarea>
                    </div>

                    <button type="submit" class="btn-primary">Save Profile Changes</button>
                </form>
            </div>
        @endif

        {{-- SECTION 3: ACTIVITY TIMELINE --}}
        @if ($sectionName === 'activity')
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #edf2f7;padding-bottom:15px;">
                    <h2 style="margin:0;font-size:18px;font-weight:600;">Personal Timeline Log</h2>
                    <form action="{{ route('account.show', 'activity') }}" method="get" style="display:flex;gap:10px;">
                        <input type="text" name="q" class="form-control" style="height:38px;width:200px;" placeholder="Search activities..." value="{{ request('q') }}">
                        <button type="submit" class="btn-primary" style="padding:8px 16px;height:38px;">Filter</button>
                    </form>
                </div>

                <div class="activity-timeline" style="margin-top:30px;">
                    @forelse($activities as $activity)
                        <div class="activity-item">
                            <span class="activity-time">{{ $activity->occurred_at->timezone($pref->timezone ?? 'Africa/Nairobi')->format('d M Y, h:i A') }} ({{ $activity->occurred_at->timezone($pref->timezone ?? 'Africa/Nairobi')->diffForHumans() }})</span>
                            <div class="activity-desc">
                                Action <strong>{{ $activity->action }}</strong> performed.
                                <div style="font-size:12px;color:#718096;margin-top:4px;">IP Address: {{ $activity->ip_address }} • User Agent: {{ $activity->user_agent }}</div>
                            </div>
                        </div>
                    @empty
                        <p style="color:#a0aec0;font-size:14px;margin-top:10px;">No matching activities found.</p>
                    @endforelse
                </div>

                <div style="margin-top:30px;">
                    {{ $activities->links() }}
                </div>
            </div>
        @endif

        {{-- SECTION 4: MY CALENDAR --}}
        @if ($sectionName === 'calendar')
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h2 style="margin:0;font-size:18px;font-weight:600;">Personal Event Calendar</h2>
                    <div style="display:flex;gap:10px;">
                        @if ($connection)
                            <form action="{{ route('account.calendar.google.sync') }}" method="post" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-primary" style="background:#4285f4;padding:8px 16px;">Sync Google</button>
                            </form>
                            <form action="{{ route('account.calendar.google.disconnect') }}" method="post" style="display:inline;">
                                @csrf
                                <button type="submit" style="background:transparent;border:1px solid #cbd5e0;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:600;color:#718096;">Disconnect Google</button>
                            </form>
                        @else
                            <form action="{{ route('account.calendar.google.connect') }}" method="post">
                                @csrf
                                <button type="submit" class="btn-primary" style="background:#4285f4;padding:8px 16px;">Connect Google Calendar</button>
                            </form>
                        @endif
                        <button class="btn-primary" style="padding:8px 16px;" onclick="document.getElementById('createEventModal').style.display='grid'">Add Event</button>
                    </div>
                </div>

                {{-- Interactive Calendar View (Mock month view for simplicity but looks highly realistic) --}}
                @php($startOfMonth = now()->startOfMonth())
                @php($daysInMonth = now()->daysInMonth)
                @php($dayOfWeekOffset = $startOfMonth->dayOfWeek)
                <div style="display:flex;justify-content:space-between;align-items:center;background:#f7fafc;padding:10px 20px;border-radius:8px;border:1px solid #edf2f7;margin-bottom:15px;">
                    <strong style="color:#2d3748;font-size:16px;">{{ now()->format('F Y') }}</strong>
                </div>

                <div class="custom-calendar">
                    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                        <div class="calendar-header">{{ $dayName }}</div>
                    @endforeach

                    @for($i = 0; $i < $dayOfWeekOffset; $i++)
                        <div class="calendar-day" style="opacity:0.4;cursor:default;"></div>
                    @endfor

                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php($currentDate = now()->startOfMonth()->addDays($day - 1))
                        @php($isToday = $currentDate->isToday())
                        <div class="calendar-day {{ $isToday ? 'today' : '' }}" onclick="openAddEventForDay('{{ $currentDate->format('Y-m-d') }}')">
                            <span class="calendar-day-num">{{ $day }}</span>
                            
                            @foreach($events as $event)
                                @if ($event->start_time->format('Y-m-d') === $currentDate->format('Y-m-d'))
                                    <div class="calendar-event-pill" style="background:{{ $event->color ?: '#1a778b' }};" onclick="event.stopPropagation(); openEditEventModal({{ $event }})">
                                        {{ $event->title }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endfor
                </div>

                {{-- CREATE EVENT MODAL --}}
                <div id="createEventModal" class="modal-overlay">
                    <div class="modal-card">
                        <div class="modal-header">
                            <h3>Create New Event</h3>
                            <button class="modal-close" onclick="document.getElementById('createEventModal').style.display='none'"><i data-lucide="x"></i></button>
                        </div>
                        <form action="{{ route('account.calendar.events.store') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="event_title">Title</label>
                                <input id="event_title" type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="event_desc">Description</label>
                                <textarea id="event_desc" name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="grid4" style="grid-template-columns:1fr 1fr;gap:15px;">
                                <div class="form-group">
                                    <label for="event_start">Start Date & Time</label>
                                    <input id="event_start" type="datetime-local" name="start_time" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="event_end">End Date & Time</label>
                                    <input id="event_end" type="datetime-local" name="end_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="grid4" style="grid-template-columns:1fr 1fr;gap:15px;">
                                <div class="form-group">
                                    <label for="event_cat">Category</label>
                                    <select id="event_cat" name="category" class="form-control">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat['name'] }}">{{ $cat['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="event_reminder">Reminder (Minutes Before)</label>
                                    <input id="event_reminder" type="number" name="reminder_minutes" class="form-control" value="30">
                                </div>
                            </div>
                            <div class="form-group">
                                <label><input type="checkbox" name="is_all_day" value="1"> All-day activity</label>
                            </div>
                            <button type="submit" class="btn-primary">Create Event</button>
                        </form>
                    </div>
                </div>

                {{-- EDIT EVENT MODAL --}}
                <div id="editEventModal" class="modal-overlay">
                    <div class="modal-card">
                        <div class="modal-header">
                            <h3>Edit Calendar Event</h3>
                            <button class="modal-close" onclick="document.getElementById('editEventModal').style.display='none'"><i data-lucide="x"></i></button>
                        </div>
                        <form id="editEventForm" action="" method="post">
                            @csrf @method('put')
                            <div class="form-group">
                                <label for="edit_event_title">Title</label>
                                <input id="edit_event_title" type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_event_desc">Description</label>
                                <textarea id="edit_event_desc" name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="grid4" style="grid-template-columns:1fr 1fr;gap:15px;">
                                <div class="form-group">
                                    <label for="edit_event_start">Start Date & Time</label>
                                    <input id="edit_event_start" type="datetime-local" name="start_time" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_event_end">End Date & Time</label>
                                    <input id="edit_event_end" type="datetime-local" name="end_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="grid4" style="grid-template-columns:1fr 1fr;gap:15px;">
                                <div class="form-group">
                                    <label for="edit_event_cat">Category</label>
                                    <select id="edit_event_cat" name="category" class="form-control">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat['name'] }}">{{ $cat['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit_event_reminder">Reminder (Minutes Before)</label>
                                    <input id="edit_event_reminder" type="number" name="reminder_minutes" class="form-control">
                                </div>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;">
                                <button type="submit" class="btn-primary">Save Changes</button>
                        </form>
                        <form id="deleteEventForm" action="" method="post" style="display:inline;">
                            @csrf @method('delete')
                            <button type="submit" style="background:#e53e3e;color:#fff;border:0;border-radius:6px;padding:12px 24px;font-weight:600;cursor:pointer;">Cancel Event</button>
                        </form>
                            </div>
                    </div>
                </div>
            </div>
            <script>
                function openAddEventForDay(date) {
                    document.getElementById('event_start').value = date + 'T09:00';
                    document.getElementById('event_end').value = date + 'T10:00';
                    document.getElementById('createEventModal').style.display = 'grid';
                }
                function openEditEventModal(event) {
                    document.getElementById('edit_event_title').value = event.title;
                    document.getElementById('edit_event_desc').value = event.description || '';
                    document.getElementById('edit_event_start').value = event.start_time.substring(0, 16);
                    document.getElementById('edit_event_end').value = event.end_time.substring(0, 16);
                    document.getElementById('edit_event_cat').value = event.category;
                    document.getElementById('edit_event_reminder').value = event.reminder_minutes || '';
                    
                    document.getElementById('editEventForm').action = '/account/calendar/events/' + event.id;
                    document.getElementById('deleteEventForm').action = '/account/calendar/events/' + event.id;
                    
                    document.getElementById('editEventModal').style.display = 'grid';
                }
            </script>
        @endif

        {{-- SECTION 5: MY FILES --}}
        @if ($sectionName === 'files')
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #edf2f7;padding-bottom:15px;margin-bottom:20px;">
                    <h2 style="margin:0;font-size:18px;font-weight:600;">Personal File Manager</h2>
                    <div style="display:flex;gap:10px;">
                        <button class="btn-primary" style="padding:8px 16px;background:#718096;" onclick="document.getElementById('createFolderModal').style.display='grid'">New Folder</button>
                        <button class="btn-primary" style="padding:8px 16px;" onclick="document.getElementById('uploadFileModal').style.display='grid'">Upload File</button>
                    </div>
                </div>

                {{-- Quota Meter --}}
                <div style="background:#f7fafc;border:1px solid #edf2f7;border-radius:10px;padding:15px;margin-bottom:25px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:8px;">
                        <span>Storage Allocation (100MB Total Capacity)</span>
                        <span>{{ round($usedBytes / (1024 * 1024), 2) }} MB of {{ $quotaBytes / (1024 * 1024) }} MB used ({{ $percentUsed }}%)</span>
                    </div>
                    <div style="width:100%;height:10px;background:#e2e8f0;border-radius:5px;overflow:hidden;">
                        <div style="width:{{ $percentUsed }}%;height:100%;background:linear-gradient(90deg, #1a778b, #0b7286);border-radius:5px;"></div>
                    </div>
                </div>

                {{-- File Tree Navigation --}}
                @if ($currentFolder)
                    <div style="margin-bottom:15px;">
                        <a href="{{ route('account.show', 'files') }}" style="color:#1a778b;text-decoration:none;font-weight:600;font-size:14px;"><i data-lucide="arrow-left" style="width:16px;vertical-align:middle;margin-right:5px;"></i>Back to root</a>
                        <span style="color:#718096;font-size:14px;margin-left:10px;">/ {{ $currentFolder->name }}</span>
                    </div>
                @endif

                <div class="file-tree">
                    @foreach($folders as $folder)
                        <div class="file-item folder" onclick="window.location='{{ route('account.show', 'files') }}?folder_id={{ $folder->id }}'">
                            <i data-lucide="folder"></i>
                            <div class="file-name">{{ $folder->name }}</div>
                            <div class="file-actions-menu" onclick="event.stopPropagation();">
                                <button class="file-actions-btn" onclick="openFileActionsModal({{ $folder }}, true)"><i data-lucide="more-vertical" style="width:16px;"></i></button>
                            </div>
                        </div>
                    @endforeach

                    @foreach($files as $file)
                        <div class="file-item file" onclick="window.location='{{ route('account.files.download', $file->id) }}'">
                            <i data-lucide="file"></i>
                            <div class="file-name">{{ $file->name }}</div>
                            <div style="font-size:10px;color:#a0aec0;margin-top:4px;">{{ round($file->size / 1024, 1) }} KB</div>
                            <div class="file-actions-menu" onclick="event.stopPropagation();">
                                <button class="file-actions-btn" onclick="openFileActionsModal({{ $file }}, false)"><i data-lucide="more-vertical" style="width:16px;"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(count($folders) === 0 && count($files) === 0)
                    <p style="color:#a0aec0;font-size:14px;text-align:center;margin:40px 0;">This directory is empty.</p>
                @endif

                {{-- Recycle Bin View --}}
                @if(count($trashedFiles) > 0)
                    <h3 style="font-size:15px;font-weight:600;margin-top:40px;border-bottom:1px solid #edf2f7;padding-bottom:8px;">Recycle Bin (Soft Deleted Files)</h3>
                    <div style="display:grid;grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));gap: 15px;margin-top:15px;">
                        @foreach($trashedFiles as $trash)
                            <div style="background:#fffaf0;border:1px solid #feebc8;border-radius:8px;padding:12px 15px;display:flex;justify-content:space-between;align-items:center;">
                                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;">
                                    <strong style="font-size:13px;color:#7b341e;display:block;">{{ $trash->name }}</strong>
                                    <small style="color:#c05621;font-size:11px;">Deleted {{ $trash->deleted_at->diffForHumans() }}</small>
                                </div>
                                <div style="display:flex;gap:5px;">
                                    <form action="{{ route('account.files.restore', $trash->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="btn-primary" style="padding:4px 8px;font-size:11px;background:#319795;"><i data-lucide="rotate-ccw" style="width:14px;"></i></button>
                                    </form>
                                    <form action="{{ route('account.files.permanent', $trash->id) }}" method="post">
                                        @csrf @method('delete')
                                        <button type="submit" class="btn-primary" style="padding:4px 8px;font-size:11px;background:#e53e3e;"><i data-lucide="trash-2" style="width:14px;"></i></button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- File Logs --}}
                <h3 style="font-size:15px;font-weight:600;margin-top:40px;border-bottom:1px solid #edf2f7;padding-bottom:8px;">Private Storage Audit Logs</h3>
                <div class="table-wrap" style="margin-top:15px;">
                    <table>
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Action</th>
                                <th>Size</th>
                                <th>Result</th>
                                <th>Occurred At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fileLogs as $log)
                                <tr>
                                    <td>{{ $log->file_name }}</td>
                                    <td><span class="badge" style="background:#e2e8f0;color:#4a5568;">{{ strtoupper($log->action) }}</span></td>
                                    <td>{{ round($log->file_size / 1024, 1) }} KB</td>
                                    <td><strong style="color:{{ $log->result==='success' ? '#319795' : '#e53e3e' }};">{{ strtoupper($log->result) }}</strong></td>
                                    <td>{{ $log->occurred_at->timezone($pref->timezone ?? 'Africa/Nairobi')->format('d M, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty">No storage logs registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- CREATE FOLDER MODAL --}}
                <div id="createFolderModal" class="modal-overlay">
                    <div class="modal-card">
                        <div class="modal-header">
                            <h3>Create New Folder</h3>
                            <button class="modal-close" onclick="document.getElementById('createFolderModal').style.display='none'"><i data-lucide="x"></i></button>
                        </div>
                        <form action="{{ route('account.files.folder') }}" method="post">
                            @csrf
                            <input type="hidden" name="folder_id" value="{{ request('folder_id') }}">
                            <div class="form-group">
                                <label for="folder_name">Folder Name</label>
                                <input id="folder_name" type="text" name="folder_name" class="form-control" required>
                            </div>
                            <button type="submit" class="btn-primary">Create Folder</button>
                        </form>
                    </div>
                </div>

                {{-- UPLOAD FILE MODAL --}}
                <div id="uploadFileModal" class="modal-overlay">
                    <div class="modal-card">
                        <div class="modal-header">
                            <h3>Upload Private File</h3>
                            <button class="modal-close" onclick="document.getElementById('uploadFileModal').style.display='none'"><i data-lucide="x"></i></button>
                        </div>
                        <form action="{{ route('account.files.upload') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="folder_id" value="{{ request('folder_id') }}">
                            <div class="form-group" id="drop-zone" style="border: 2px dashed #cbd5e0;padding:30px;text-align:center;border-radius:8px;background:#f7fafc;cursor:pointer;margin-bottom:20px;">
                                <i data-lucide="upload-cloud" style="width:40px;height:40px;color:#a0aec0;margin-bottom:10px;"></i>
                                <p style="margin:0;font-size:14px;color:#718096;">Drag & Drop your file here or Click to select</p>
                                <input id="file-input" type="file" name="file" style="display:none;" required onchange="updateFileNameLabel(this)">
                                <div id="file-name-label" style="font-weight:600;margin-top:10px;color:#1a778b;"></div>
                            </div>
                            <button type="submit" class="btn-primary">Upload Securely</button>
                        </form>
                    </div>
                </div>

                {{-- FILE ACTIONS MODAL --}}
                <div id="fileActionsModal" class="modal-overlay">
                    <div class="modal-card">
                        <div class="modal-header">
                            <h3 id="actions-modal-title">Manage File</h3>
                            <button class="modal-close" onclick="document.getElementById('fileActionsModal').style.display='none'"><i data-lucide="x"></i></button>
                        </div>
                        
                        <form id="renameFileForm" action="" method="post" class="form-group">
                            @csrf
                            <label for="rename_input">Rename</label>
                            <div style="display:flex;gap:10px;">
                                <input id="rename_input" type="text" name="name" class="form-control" required>
                                <button type="submit" class="btn-primary" style="padding:0 20px;">Save</button>
                            </div>
                        </form>

                        <form id="moveFileForm" action="" method="post" class="form-group" style="border-top:1px solid #edf2f7;padding-top:20px;">
                            @csrf
                            <label for="move_select">Move to Folder</label>
                            <div style="display:flex;gap:10px;">
                                <select id="move_select" name="parent_id" class="form-control">
                                    <option value="">[Root Directory]</option>
                                    @foreach(\App\Models\PersonalFile::where('user_id', $user->id)->where('is_folder', true)->get() as $f)
                                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-primary" style="padding:0 20px;">Move</button>
                            </div>
                        </form>

                        <div style="border-top:1px solid #edf2f7;padding-top:20px;display:flex;justify-content:flex-end;">
                            <form id="deleteFileForm" action="" method="post">
                                @csrf @method('delete')
                                <button type="submit" style="background:#e53e3e;color:#fff;border:0;border-radius:6px;padding:10px 20px;font-weight:600;cursor:pointer;">Send to Trash</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function updateFileNameLabel(input) {
                    if (input.files && input.files[0]) {
                        document.getElementById('file-name-label').innerText = input.files[0].name;
                    }
                }
                const dropZone = document.getElementById('drop-zone');
                const fileInput = document.getElementById('file-input');
                dropZone?.addEventListener('click', () => fileInput.click());
                dropZone?.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = '#1a778b'; });
                dropZone?.addEventListener('dragleave', () => dropZone.style.borderColor = '#cbd5e0');
                dropZone?.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.style.borderColor = '#cbd5e0';
                    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                        fileInput.files = e.dataTransfer.files;
                        updateFileNameLabel(fileInput);
                    }
                });

                function openFileActionsModal(file, isFolder) {
                    document.getElementById('actions-modal-title').innerText = 'Manage ' + (isFolder ? 'Folder' : 'File') + ': ' + file.name;
                    document.getElementById('rename_input').value = file.name;
                    
                    document.getElementById('renameFileForm').action = '/account/files/rename/' + file.id;
                    document.getElementById('moveFileForm').action = '/account/files/move/' + file.id;
                    document.getElementById('deleteFileForm').action = '/account/files/' + file.id;
                    
                    document.getElementById('fileActionsModal').style.display = 'grid';
                }
            </script>
        @endif

        {{-- SECTION 6: MY REPORTS --}}
        @if ($sectionName === 'reports')
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #edf2f7;padding-bottom:15px;margin-bottom:20px;">
                    <h2 style="margin:0;font-size:18px;font-weight:600;">Personal Reports Centre</h2>
                    <button class="btn-primary" style="padding:8px 16px;" onclick="document.getElementById('createReportModal').style.display='grid'">New Personal Report</button>
                </div>

                <div style="display:grid;grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));gap:20px;">
                    @forelse($reports as $report)
                        <div style="background:#f8fafc;border:1px solid #edf2f7;border-radius:10px;padding:20px;position:relative;">
                            <span class="badge" style="background:#edf8f9;color:#1a778b;font-size:11px;padding:2px 6px;border-radius:4px;font-weight:600;text-transform:uppercase;float:right;">{{ $report->source }}</span>
                            <h4 style="margin:0 0 8px;font-size:16px;font-weight:600;color:#2d3748;">{{ $report->name }}</h4>
                            <p style="font-size:13px;color:#718096;margin:0 0 15px;line-height:1.4;">{{ $report->description ?: 'No description provided.' }}</p>
                            
                            @if(isset($report->options['tags']))
                                <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:15px;">
                                    @foreach($report->options['tags'] as $tag)
                                        <span style="font-size:11px;background:#e2e8f0;color:#4a5568;padding:1px 6px;border-radius:4px;font-weight:500;">#{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #edf2f7;padding-top:12px;">
                                <button class="btn-primary" style="padding:6px 12px;font-size:12px;" onclick="runReport({{ $report->id }})">Run Report</button>
                                
                                <div style="display:flex;gap:5px;">
                                    <form action="{{ route('account.reports.duplicate', $report->id) }}" method="post">
                                        @csrf
                                        <button type="submit" class="file-actions-btn" title="Duplicate"><i data-lucide="copy" style="width:15px;"></i></button>
                                    </form>
                                    <button class="file-actions-btn" title="Edit" onclick="openEditReportModal({{ $report }})"><i data-lucide="edit" style="width:15px;"></i></button>
                                    <form action="{{ route('account.reports.delete', $report->id) }}" method="post">
                                        @csrf @method('delete')
                                        <button type="submit" class="file-actions-btn" title="Delete" style="color:#e53e3e;"><i data-lucide="trash-2" style="width:15px;"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column:1/-1;text-align:center;padding:40px 0;color:#a0aec0;font-size:14px;">You have no saved personal reports. Create one to begin.</div>
                    @endforelse
                </div>

                {{-- REPORT PREVIEW MODAL --}}
                <div id="reportPreviewModal" class="modal-overlay">
                    <div class="modal-card" style="width:min(100%, 800px);">
                        <div class="modal-header">
                            <h3 id="preview-report-title">Report Data Preview</h3>
                            <button class="modal-close" onclick="document.getElementById('reportPreviewModal').style.display='none'"><i data-lucide="x"></i></button>
                        </div>
                        <div id="preview-report-content" style="max-height:400px;overflow-y:auto;border:1px solid #edf2f7;border-radius:8px;background:#f8fafc;padding:15px;">
                            {{-- Dynamic report table --}}
                        </div>
                    </div>
                </div>

                {{-- CREATE REPORT MODAL --}}
                <div id="createReportModal" class="modal-overlay">
                    <div class="modal-card">
                        <div class="modal-header">
                            <h3>Create Custom Report</h3>
                            <button class="modal-close" onclick="document.getElementById('createReportModal').style.display='none'"><i data-lucide="x"></i></button>
                        </div>
                        <form action="{{ route('account.reports.store') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="report_name">Report Name</label>
                                <input id="report_name" type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="report_desc">Description</label>
                                <textarea id="report_desc" name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="report_source">Report Source</label>
                                <select id="report_source" name="source" class="form-control" required onchange="updateColumnsSelector(this)">
                                    <option value="">Select report source...</option>
                                    @foreach($sources as $key => $src)
                                        <option value="{{ $key }}">{{ $src['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Selected Columns</label>
                                <div id="columns-selector-box" style="display:flex;gap:10px;flex-wrap:wrap;background:#f8fafc;padding:12px;border:1px solid #cbd5e0;border-radius:6px;min-height:50px;">
                                    <span style="color:#a0aec0;font-size:13px;">Select a report source first.</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Standard Tags</label>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    @foreach($standardTags as $tag)
                                        <label style="font-size:12px;background:#f7fafc;border:1px solid #cbd5e0;border-radius:4px;padding:3px 8px;cursor:pointer;">
                                            <input type="checkbox" name="tags[]" value="{{ $tag }}"> #{{ $tag }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <button type="submit" class="btn-primary">Create Report</button>
                        </form>
                    </div>
                </div>

                {{-- EDIT REPORT MODAL --}}
                <div id="editReportModal" class="modal-overlay">
                    <div class="modal-card">
                        <div class="modal-header">
                            <h3>Edit Report</h3>
                            <button class="modal-close" onclick="document.getElementById('editReportModal').style.display='none'"><i data-lucide="x"></i></button>
                        </div>
                        <form id="editReportForm" action="" method="post">
                            @csrf @method('put')
                            <div class="form-group">
                                <label for="edit_report_name">Report Name</label>
                                <input id="edit_report_name" type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_report_desc">Description</label>
                                <textarea id="edit_report_desc" name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <input id="edit_report_source" type="hidden" name="source">

                            <div class="form-group">
                                <label>Selected Columns</label>
                                <div id="edit-columns-selector-box" style="display:flex;gap:10px;flex-wrap:wrap;background:#f8fafc;padding:12px;border:1px solid #cbd5e0;border-radius:6px;min-height:50px;">
                                </div>
                            </div>

                            <button type="submit" class="btn-primary">Save Report Changes</button>
                        </form>
                    </div>
                </div>
            </div>
            <script>
                const sourceColumnsMap = {
                    'badges': ['id', 'title', 'author', 'isbn', 'category'],
                    'blogs': ['id', 'name', 'code'],
                    'cohorts': ['id', 'name', 'code'],
                    'comments': ['id', 'title', 'author'],
                    'competencies': ['id', 'name'],
                    'course_participants': ['id', 'admission_number', 'created_at'],
                    'courses': ['id', 'name', 'code'],
                    'files': ['id', 'name', 'size', 'mime_type', 'created_at'],
                    'groups': ['id', 'name'],
                    'notes': ['id', 'title'],
                    'roles': ['id', 'stakeholder_type', 'is_active'],
                    'tags': ['id', 'name'],
                    'task_logs': ['id', 'action', 'ip_address', 'occurred_at'],
                    'user_badges': ['id', 'name', 'email', 'role'],
                    'users': ['id', 'name', 'email', 'role', 'is_active'],
                    'course_ratings': ['id', 'test_score', 'exam_score'],
                };

                function updateColumnsSelector(select) {
                    const source = select.value;
                    const container = document.getElementById('columns-selector-box');
                    container.innerHTML = '';
                    if (!source || !sourceColumnsMap[source]) {
                        container.innerHTML = '<span style="color:#a0aec0;font-size:13px;">Select a report source first.</span>';
                        return;
                    }
                    sourceColumnsMap[source].forEach(col => {
                        container.innerHTML += `
                            <label style="font-size:13px;display:flex;align-items:center;gap:5px;">
                                <input type="checkbox" name="columns[]" value="${col}" checked> ${col}
                            </label>
                        `;
                    });
                }

                function openEditReportModal(report) {
                    document.getElementById('edit_report_name').value = report.name;
                    document.getElementById('edit_report_desc').value = report.description || '';
                    document.getElementById('edit_report_source').value = report.source;
                    
                    const container = document.getElementById('edit-columns-selector-box');
                    container.innerHTML = '';
                    sourceColumnsMap[report.source].forEach(col => {
                        const isChecked = report.columns.includes(col);
                        container.innerHTML += `
                            <label style="font-size:13px;display:flex;align-items:center;gap:5px;">
                                <input type="checkbox" name="columns[]" value="${col}" ${isChecked ? 'checked' : ''}> ${col}
                            </label>
                        `;
                    });

                    document.getElementById('editReportForm').action = '/account/reports/' + report.id;
                    document.getElementById('editReportModal').style.display = 'grid';
                }

                function runReport(reportId) {
                    fetch('/account/reports/' + reportId + '/run')
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) {
                                alert('Error: ' + data.error);
                                return;
                            }
                            document.getElementById('preview-report-title').innerText = 'Report: ' + data.reportName;
                            const container = document.getElementById('preview-report-content');
                            container.innerHTML = '';
                            
                            if (data.data.length === 0) {
                                container.innerHTML = '<p style="text-align:center;color:#a0aec0;margin:20px 0;">This report returned 0 records.</p>';
                            } else {
                                let tableHtml = '<table style="width:100%;font-size:13px;"><thead><tr>';
                                data.columns.forEach(col => {
                                    tableHtml += `<th style="text-align:left;padding:8px;background:#edf2f7;border-bottom:2px solid #cbd5e0;">${col}</th>`;
                                });
                                tableHtml += '</tr></thead><tbody>';
                                data.data.forEach(row => {
                                    tableHtml += '<tr>';
                                    data.columns.forEach(col => {
                                        tableHtml += `<td style="padding:8px;border-bottom:1px solid #edf2f7;">${row[col] !== null ? row[col] : '—'}</td>`;
                                    });
                                    tableHtml += '</tr>';
                                });
                                tableHtml += '</tbody></table>';
                                container.innerHTML = tableHtml;
                            }
                            document.getElementById('reportPreviewModal').style.display = 'grid';
                        });
                }
            </script>
        @endif

        {{-- SECTION 7: PREFERENCES --}}
        @if ($sectionName === 'preferences')
            <div>
                <div class="tab-container">
                    <div class="tab-btn active" onclick="switchPrefTab(event, 'pref-account')">User Account</div>
                    <div class="tab-btn" onclick="switchPrefTab(event, 'pref-comm')">Communication</div>
                    <div class="tab-btn" onclick="switchPrefTab(event, 'pref-learn')">Learning & Content</div>
                </div>

                <form action="{{ route('account.preferences') }}" method="post">
                    @csrf @method('put')

                    {{-- TAB A: ACCOUNT --}}
                    <div id="pref-account" class="tab-content active">
                        <div class="grid4" style="grid-template-columns:1fr 1fr;gap:20px;">
                            <div class="form-group">
                                <label for="pref_lang">Preferred Language</label>
                                <select id="pref_lang" name="language" class="form-control">
                                    <option value="en" @selected($pref->language === 'en')>English</option>
                                    <option value="sw" @selected($pref->language === 'sw')>Kiswahili</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="pref_tz">Preferred Timezone</label>
                                <select id="pref_tz" name="timezone" class="form-control">
                                    @foreach(timezone_identifiers_list() as $tz)
                                        <option value="{{ $tz }}" @selected(($pref->timezone ?? 'Africa/Nairobi') === $tz)>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="pref_theme">Interface Theme</label>
                            <select id="pref_theme" name="theme" class="form-control">
                                <option value="system" @selected($pref->theme === 'system')>Use System Default</option>
                                <option value="light" @selected($pref->theme === 'light')>Sleek Light Mode</option>
                                <option value="dark" @selected($pref->theme === 'dark')>Premium Dark Mode</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-top:20px;">
                            <label><input type="checkbox" name="accessibility_reduced_motion" value="1" @checked($pref->accessibility_settings['reduced_motion'] ?? false)> Enable Reduced-Motion Preferences</label>
                            <p style="font-size:12px;color:#a0aec0;margin:4px 0 0 20px;">Stops heavy animated transitions on the frontend.</p>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="accessibility_high_contrast" value="1" @checked($pref->accessibility_settings['high_contrast'] ?? false)> Enable High-Contrast Mode</label>
                            <p style="font-size:12px;color:#a0aec0;margin:4px 0 0 20px;">Optimizes layouts for high visual accessibility.</p>
                        </div>
                    </div>

                    {{-- TAB B: COMMUNICATION --}}
                    <div id="pref-comm" class="tab-content">
                        <div class="form-group">
                            <label><input type="checkbox" name="comm_email" value="1" @checked($pref->communication_settings['email'] ?? true)> Dispatch Email Notifications</label>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="comm_sms" value="1" @checked($pref->communication_settings['sms'] ?? false)> Dispatch SMS Notifications</label>
                        </div>
                        <div class="form-group">
                            <label for="comm_digest">Digest Dispatch Frequency</label>
                            <select id="comm_digest" name="comm_digest" class="form-control">
                                <option value="none" @selected(($pref->communication_settings['digest'] ?? 'none') === 'none')>Real-time (Immediate Delivery)</option>
                                <option value="daily" @selected(($pref->communication_settings['digest'] ?? '') === 'daily')>Daily Summarized Digest</option>
                                <option value="weekly" @selected(($pref->communication_settings['digest'] ?? '') === 'weekly')>Weekly Summarized Digest</option>
                            </select>
                        </div>
                        
                        <div class="grid4" style="grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
                            <div class="form-group">
                                <label for="comm_quiet_start">Quiet Hours Start</label>
                                <input id="comm_quiet_start" type="text" name="comm_quiet_start" class="form-control" placeholder="22:00" value="{{ $pref->communication_settings['quiet_hours']['start'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label for="comm_quiet_end">Quiet Hours End</label>
                                <input id="comm_quiet_end" type="text" name="comm_quiet_end" class="form-control" placeholder="07:00" value="{{ $pref->communication_settings['quiet_hours']['end'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    {{-- TAB C: LEARNING & CONTENT --}}
                    <div id="pref-learn" class="tab-content">
                        <div class="form-group">
                            <label for="learn_forum">Forum Dispatch Preferences</label>
                            <select id="learn_forum" name="learn_forum" class="form-control">
                                <option value="digest" @selected(($pref->learning_settings['forum'] ?? 'digest') === 'digest')>Daily digest of post headlines</option>
                                <option value="all" @selected(($pref->learning_settings['forum'] ?? '') === 'all')>All forum replies individually</option>
                                <option value="none" @selected(($pref->learning_settings['forum'] ?? '') === 'none')>No forum subscription notifications</option>
                            </select>
                        </div>

                        <div class="grid4" style="grid-template-columns:1fr 1fr;gap:20px;">
                            <div class="form-group">
                                <label for="learn_editor">Rich Text Editor Option</label>
                                <select id="learn_editor" name="learn_editor" class="form-control">
                                    <option value="rich" @selected(($pref->learning_settings['editor'] ?? 'rich') === 'rich')>Rich visual HTML text editor</option>
                                    <option value="simple" @selected(($pref->learning_settings['editor'] ?? '') === 'simple')>Simple plaintext editor</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="learn_calendar">Default Calendar Mode</label>
                                <select id="learn_calendar" name="learn_calendar" class="form-control">
                                    <option value="month" @selected(($pref->learning_settings['calendar'] ?? 'month') === 'month')>Month grid display</option>
                                    <option value="week" @selected(($pref->learning_settings['calendar'] ?? '') === 'week')>Week schedule display</option>
                                    <option value="day" @selected(($pref->learning_settings['calendar'] ?? '') === 'day')>Day agenda display</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid4" style="grid-template-columns:1fr 1fr;gap:20px;">
                            <div class="form-group">
                                <label for="learn_content_bank">Content Bank Scoping</label>
                                <select id="learn_content_bank" name="learn_content_bank" class="form-control">
                                    <option value="internal" @selected(($pref->learning_settings['content_bank'] ?? 'internal') === 'internal')>Limit searches to internal repositories</option>
                                    <option value="external" @selected(($pref->learning_settings['content_bank'] ?? '') === 'external')>Allow external indexed sources</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="learn_file">File Manager Layout</label>
                                <select id="learn_file" name="learn_file" class="form-control">
                                    <option value="grid" @selected(($pref->learning_settings['file'] ?? 'grid') === 'grid')>Dynamic Grid Cards</option>
                                    <option value="list" @selected(($pref->learning_settings['file'] ?? '') === 'list')>Detailed Table List</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="privacy_discoverable" value="1" @checked($pref->privacy_settings['profile_discoverable'] ?? false)> Allow peer discovery of profile</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top:20px;">Save All Preferences</button>
                </form>
            </div>
            <script>
                function switchPrefTab(event, tabId) {
                    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                    event.target.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                }
            </script>
        @endif

        {{-- SECTION 8: SECURITY CENTRE --}}
        @if ($sectionName === 'security')
            <div>
                {{-- Change Password --}}
                <h3 style="font-size:16px;font-weight:600;border-bottom:1px solid #edf2f7;padding-bottom:10px;margin-bottom:20px;">Change Password</h3>
                <form action="{{ route('account.security.password') }}" method="post" style="margin-bottom:40px;">
                    @csrf
                    <div class="form-group">
                        <label for="curr_pw">Current Password</label>
                        <input id="curr_pw" type="password" name="current_password" class="form-control" required style="width:min(100%, 400px);">
                    </div>
                    <div class="grid4" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));gap: 20px;max-width:800px;">
                        <div class="form-group">
                            <label for="new_pw">New Password</label>
                            <input id="new_pw" type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="new_pw_conf">Confirm New Password</label>
                            <input id="new_pw_conf" type="password" name="new_password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="revoke_others" value="1"> Revoke all other active sessions</label>
                    </div>
                    <button type="submit" class="btn-primary">Change Password securely</button>
                </form>

                {{-- MFA --}}
                <h3 style="font-size:16px;font-weight:600;border-bottom:1px solid #edf2f7;padding-bottom:10px;margin-bottom:20px;margin-top:40px;">Multi-factor Authentication (MFA)</h3>
                <div style="background:#f8fafc;border:1px solid #edf2f7;padding:20px;border-radius:10px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <strong style="display:block;font-size:14px;color:#2d3748;">TOTP Google Authenticator Setup</strong>
                        <small style="color:#718096;font-size:13px;">Secure your account access with rolling time-based security pins.</small>
                    </div>
                    <form action="{{ route('account.security.mfa') }}" method="post">
                        @csrf
                        <button type="submit" class="btn-primary" style="background:{{ $user->mfa_enabled_at ? '#e53e3e' : '#319795' }};">
                            {{ $user->mfa_enabled_at ? 'Disable MFA' : 'Enable MFA' }}
                        </button>
                    </form>
                </div>

                {{-- Security Keys --}}
                <h3 style="font-size:16px;font-weight:600;border-bottom:1px solid #edf2f7;padding-bottom:10px;margin-bottom:20px;margin-top:40px;">Passkeys & FIDO2 Security Keys</h3>
                <div style="margin-bottom:20px;">
                    <form action="{{ route('account.security.keys.register') }}" method="post" style="display:flex;gap:10px;">
                        @csrf
                        <input type="text" name="key_name" class="form-control" placeholder="Security key label (e.g. Yubikey 5C)" required style="width:min(100%, 300px);height:40px;">
                        <button type="submit" class="btn-primary" style="padding:0 20px;height:40px;">Register Key</button>
                    </form>
                </div>
                <div class="table-wrap" style="margin-bottom:40px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Name / Label</th>
                                <th>Credential Identifier</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($securityKeys as $key)
                                <tr>
                                    <td><strong>{{ $key->name }}</strong></td>
                                    <td><code>{{ $key->credential_id }}</code></td>
                                    <td>{{ $key->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <form action="{{ route('account.security.keys.delete', $key->id) }}" method="post">
                                            @csrf @method('delete')
                                            <button type="submit" style="background:transparent;border:0;color:#e53e3e;cursor:pointer;font-weight:600;">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty">No registered security keys.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Active Sessions --}}
                <h3 style="font-size:16px;font-weight:600;border-bottom:1px solid #edf2f7;padding-bottom:10px;margin-bottom:20px;margin-top:40px;">Active Login Sessions</h3>
                <div style="display:flex;justify-content:flex-end;margin-bottom:15px;">
                    <form action="{{ route('account.security.sessions.revoke-others') }}" method="post">
                        @csrf
                        <button type="submit" style="background:#e53e3e;color:#fff;border:0;border-radius:6px;padding:8px 16px;font-weight:600;cursor:pointer;">Revoke Other Sessions</button>
                    </form>
                </div>
                <div class="table-wrap" style="margin-bottom:40px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Session ID</th>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <th>Last Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $sess)
                                <tr>
                                    <td><code>{{ substr($sess->id, 0, 8) }}...</code></td>
                                    <td>{{ $sess->ip_address }}</td>
                                    <td style="font-size:12px;color:#718096;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $sess->user_agent }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::createFromTimestamp($sess->last_activity)->timezone($pref->timezone ?? 'Africa/Nairobi')->diffForHumans() }}</td>
                                    <td>
                                        @if($sess->id !== request()->session()->getId())
                                            <form action="{{ route('account.security.sessions.revoke', $sess->id) }}" method="post">
                                                @csrf
                                                <button type="submit" style="background:transparent;border:0;color:#e53e3e;cursor:pointer;font-weight:600;">Revoke</button>
                                            </form>
                                        @else
                                            <strong style="color:#319795;">Current Active Session</strong>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty">No tracked session activities.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Trusted Devices --}}
                <h3 style="font-size:16px;font-weight:600;border-bottom:1px solid #edf2f7;padding-bottom:10px;margin-bottom:20px;margin-top:40px;">Trusted Device Registry</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Device Name</th>
                                <th>Browser</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Last Authenticated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trustedDevices as $dev)
                                <tr>
                                    <td><strong>{{ $dev->device_name }}</strong></td>
                                    <td>{{ $dev->browser }}</td>
                                    <td>{{ $dev->ip_address }}</td>
                                    <td>{{ $dev->location ?: 'Unknown' }}</td>
                                    <td>{{ $dev->last_used_at->timezone($pref->timezone ?? 'Africa/Nairobi')->format('d M Y, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty">No trusted devices registered. Check "Remember Me" during login to register.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- SECTION 9: HELP & SUPPORT --}}
        @if ($sectionName === 'support')
            <div>
                <h2 style="margin:0 0 10px;font-size:18px;font-weight:600;">Support Desk ticket creation</h2>
                <p style="font-size:14px;color:#718096;margin:0 0 25px;line-height:1.4;">Submit an inquiry, defect ticket, or help ticket to our administrative team directly from your dashboard.</p>

                <form action="{{ route('account.support.ticket') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="ticket_cat">Ticket Scoping Category</label>
                        <select id="ticket_cat" name="category" class="form-control" style="width:min(100%, 300px);" required>
                            <option value="Technical">Technical System Bug</option>
                            <option value="Billing">Finance & Fees Inquiry</option>
                            <option value="Academic">Academic & Enrollment Issues</option>
                            <option value="General">General Inquiry</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ticket_subject">Subject Summary</label>
                        <input id="ticket_subject" type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="ticket_msg">Detail Message</label>
                        <textarea id="ticket_msg" name="message" class="form-control" rows="6" required placeholder="Describe your issue with as much detail as possible..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary">Submit Ticket</button>
                </form>
            </div>
        @endif

        {{-- SECTION 10: GRADES --}}
        @if ($sectionName === 'grades')
            <section class="panel"><div class="panel-head"><h2>Academic grades</h2></div><div class="table-wrap"><table><thead><tr><th>Subject</th><th>Test</th><th>Exam</th><th>Total</th></tr></thead><tbody>@forelse($results as $result)<tr><td>{{ $result->subject?->name }}</td><td>{{ $result->test_score }}</td><td>{{ $result->exam_score }}</td><td><strong>{{ $result->test_score+$result->exam_score }}</strong></td></tr>@empty<tr><td colspan="4" class="empty">No grades are available for this account.</td></tr>@endforelse</tbody></table></div></section>
        @endif
    </div>
</div>
@endsection
