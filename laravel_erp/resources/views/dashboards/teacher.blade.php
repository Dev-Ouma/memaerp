<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
    <div>
        <div class="eyebrow">Faculty and Lecturer Workspace</div>
        <h1 class="heading" style="margin:2px 0 4px;">Welcome back, {{ auth()->user()->first_name }}.</h1>
        <p class="sub" style="margin:0;">Your teaching allocation and learner performance workspace.</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span class="badge" style="background:#e6f1eb;color:#0A3E50;font-weight:700;padding:6px 12px;border-radius:20px;border:1px solid #c8ded4;font-size:12px;">
            <i data-lucide="graduation-cap" style="width:14px;height:14px;margin-right:4px;vertical-align:middle;"></i>
            Faculty Member
        </span>
        <a href="{{ route('subjects.index') }}" class="btn" style="background:#0A3E50;color:#fff;font-size:12px;padding:8px 14px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            <i data-lucide="plus-circle" style="width:14px;height:14px;"></i> Manage Course Units
        </a>
    </div>
</div>

<!-- Faculty Header Banner -->
<section class="panel" style="margin-bottom:18px;background:linear-gradient(135deg, #0A3E50 0%, #154c60 100%);color:#fff;border-radius:12px;border:0;box-shadow:0 6px 20px rgba(10,62,80,0.15);">
    <div style="padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="width:52px;height:52px;border-radius:12px;background:#1E8449;color:#ffffff;display:grid;place-items:center;font-size:22px;font-weight:900;box-shadow:0 4px 12px rgba(30,132,73,0.3);">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name ?: '', 0, 1)) }}
            </div>
            <div>
                <h2 style="color:#ffffff;margin:0 0 4px;font-size:18px;font-weight:750;">{{ auth()->user()->name }}</h2>
                <div style="color:#b9ccc3;font-size:13px;display:flex;gap:14px;flex-wrap:wrap;">
                    <span><strong style="color:#ffffff;">Department:</strong> {{ $teacher?->course?->name ?? 'General Academic Faculty' }}</span>
                    <span>•</span>
                    <span><strong style="color:#ffffff;">Email:</strong> {{ auth()->user()->email }}</span>
                    <span>•</span>
                    <span><strong style="color:#ffffff;">Teaching Units:</strong> {{ $subjects->count() }} active</span>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:10px;">
            <div style="text-align:right;background:rgba(255,255,255,0.1);padding:8px 16px;border-radius:8px;border:1px solid rgba(255,255,255,0.15);">
                <div style="font-size:11px;color:#b9ccc3;text-transform:uppercase;letter-spacing:0.5px;">Class Average</div>
                <div style="font-size:20px;font-weight:800;color:#ffffff;">{{ $classAverage }}%</div>
            </div>
        </div>
    </div>
</section>

<!-- 4 Key Stat Cards -->
<div class="grid4" style="margin-bottom:20px;">
    <div class="stat">
        <div class="stat-head">
            <span>Primary Allocation</span>
            <i data-lucide="building-2" style="color:#0A3E50;"></i>
        </div>
        <b class="compact-value">{{ $teacher?->course?->name ?? 'Unassigned' }}</b>
        <small>Assigned faculty department</small>
    </div>

    <div class="stat">
        <div class="stat-head">
            <span>Assigned Units</span>
            <i data-lucide="book-open" style="color:#1E8449;"></i>
        </div>
        <b>{{ $subjects->count() }}</b>
        <small>Curriculum units taught</small>
    </div>

    <div class="stat">
        <div class="stat-head">
            <span>Enrolled Students</span>
            <i data-lucide="users" style="color:#E67E22;"></i>
        </div>
        <b>{{ $studentCount }}</b>
        <small>Active learners in department</small>
    </div>

    <div class="stat">
        <div class="stat-head">
            <span>Results Evaluated</span>
            <i data-lucide="chart-column" style="color:#0A3E50;"></i>
        </div>
        <b>{{ $gradedCount }}</b>
        <small>Submissions graded & published</small>
    </div>
</div>

<div class="cols" style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <!-- Main Left Column: Teaching Allocation & Assigned Students -->
    <div>
        <!-- Teaching Allocation Table -->
        <section class="panel" style="margin-bottom:20px;">
            <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2>Teaching allocation & syllabus units</h2>
                    <small style="color:var(--muted)">All academic course units assigned for lecture and assessment</small>
                </div>
                <span class="badge" style="background:#f3f4f6;color:#374151;">{{ $subjects->count() }} Units</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Unit Code</th>
                            <th>Subject Title</th>
                            <th>Target Programme</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjects as $subject)
                            <tr>
                                <td>
                                    <span class="badge" style="background:#e6f1eb;color:#0A3E50;font-weight:750;padding:4px 8px;border-radius:6px;font-family:monospace;">
                                        {{ $subject->code }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color:#0A3E50;">{{ $subject->name }}</strong>
                                </td>
                                <td>
                                    <span style="font-size:13px;color:#4b5563;">{{ $subject->course->name ?? 'General Course' }}</span>
                                </td>
                                <td style="text-align:center;">
                                    <a href="{{ route('results.index') }}" class="btn" style="background:#0A3E50;color:#fff;font-size:11px;padding:5px 10px;border-radius:6px;text-decoration:none;">
                                        Enter Marks
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty" style="padding:28px 16px;text-align:center;">
                                    <i data-lucide="book-x" style="width:32px;height:32px;color:#9ca3af;margin-bottom:6px;"></i>
                                    <div>No teaching units assigned yet.</div>
                                    <small style="color:#6b7280;">Contact the Academic Registrar to configure your teaching allocations.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Student Roster Overview -->
        @if($assignedStudents->isNotEmpty())
            <section class="panel">
                <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h2>Assigned student cohort roster</h2>
                        <small style="color:var(--muted)">Active enrolled learners in your department</small>
                    </div>
                    <a href="{{ route('students.index') }}" style="font-size:12px;color:#0A3E50;text-decoration:none;font-weight:700;">View all &rarr;</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Admission Number</th>
                                <th>Programme</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedStudents as $student)
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <span class="avatar" style="background:#e6f1eb;color:#0A3E50;font-size:11px;font-weight:700;width:28px;height:28px;border-radius:6px;display:grid;place-items:center;">
                                                {{ strtoupper(substr($student->user->first_name ?: 'S', 0, 1)) }}
                                            </span>
                                            <div>
                                                <strong style="color:#0A3E50;">{{ $student->user->name }}</strong>
                                                <div style="font-size:11px;color:#6b7280;">{{ $student->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-family:monospace;font-weight:600;color:#374151;">{{ $student->admission_number }}</span>
                                    </td>
                                    <td>
                                        <span style="font-size:12px;color:#4b5563;">{{ $student->course?->name ?? 'Course' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>

    <!-- Right Column: Recent Results & Faculty Actions -->
    <div>
        <!-- Recent Results -->
        <section class="panel" style="margin-bottom:20px;">
            <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;">
                <h2>Recent results recorded</h2>
                <a href="{{ route('results.index') }}" style="font-size:11px;color:#0A3E50;text-decoration:none;font-weight:700;">All results &rarr;</a>
            </div>
            <div class="panel-body" style="padding:14px 16px;">
                @forelse($recentResults as $result)
                    @php($tot = (float)($result->test_score + $result->exam_score))
                    <div style="padding:10px 0;border-bottom:1px solid #edf2f7;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <strong style="font-size:13px;color:#0A3E50;display:block;">{{ $result->student->user->name }}</strong>
                            <small style="color:#64748b;">{{ $result->subject->name }} ({{ $result->subject->code }})</small>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge" style="background:{{ $tot >= 70 ? '#e6f4ea' : ($tot >= 50 ? '#f0f9ff' : '#fef2f2') }};color:{{ $tot >= 70 ? '#1E8449' : ($tot >= 50 ? '#0369a1' : '#dc2626') }};font-weight:800;padding:4px 8px;">
                                {{ $tot }}%
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:24px 8px;color:#64748b;font-size:13px;">
                        <i data-lucide="clipboard-list" style="width:28px;height:28px;color:#94a3b8;margin-bottom:6px;"></i>
                        <div>No grades recorded yet.</div>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Faculty Quick Tools -->
        <section class="panel" style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div class="panel-head">
                <h2>Faculty quick tools</h2>
            </div>
            <div class="panel-body" style="padding:12px 16px;display:grid;gap:8px;">
                <a href="{{ route('results.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;text-decoration:none;color:#0A3E50;font-weight:600;font-size:13px;">
                    <i data-lucide="edit-3" style="color:#0A3E50;width:18px;height:18px;"></i>
                    <span>Gradebook & Assessment Entry</span>
                </a>
                <a href="{{ route('subjects.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;text-decoration:none;color:#0A3E50;font-weight:600;font-size:13px;">
                    <i data-lucide="layers" style="color:#1E8449;width:18px;height:18px;"></i>
                    <span>Subject Course Units</span>
                </a>
                <a href="{{ route('account.show', 'calendar') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;text-decoration:none;color:#0A3E50;font-weight:600;font-size:13px;">
                    <i data-lucide="calendar" style="color:#E67E22;width:18px;height:18px;"></i>
                    <span>Lecture Schedule & Calendar</span>
                </a>
                <a href="{{ route('account.show', 'files') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;text-decoration:none;color:#0A3E50;font-weight:600;font-size:13px;">
                    <i data-lucide="folder" style="color:#64748b;width:18px;height:18px;"></i>
                    <span>Teaching Resources & Files</span>
                </a>
            </div>
        </section>
    </div>
</div>
