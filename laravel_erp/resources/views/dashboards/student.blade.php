<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
    <div>
        <div class="eyebrow">Student portal</div>
        <h1 class="heading" style="margin:2px 0 4px;">Welcome back, {{ auth()->user()->first_name }}.</h1>
        <p class="sub" style="margin:0;">Your course progress, attendance, and latest assessment results.</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span class="badge" style="background:#e6f4ea;color:#1E8449;font-weight:700;padding:6px 12px;border-radius:20px;border:1px solid #c2e7cf;font-size:12px;">
            <i data-lucide="shield-check" style="width:14px;height:14px;margin-right:4px;vertical-align:middle;"></i>
            {{ $academicStanding }}
        </span>
        <a href="{{ route('subjects.index') }}" class="btn" style="background:#0A3E50;color:#fff;font-size:12px;padding:8px 14px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            <i data-lucide="book-open" style="width:14px;height:14px;"></i> View Course Units
        </a>
    </div>
</div>

<!-- Student Identity Banner -->
<section class="panel" style="margin-bottom:18px;background:linear-gradient(135deg, #0A3E50 0%, #0d4e64 100%);color:#fff;border-radius:12px;border:0;box-shadow:0 6px 20px rgba(10,62,80,0.15);">
    <div style="padding:20px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="width:52px;height:52px;border-radius:12px;background:#E67E22;color:#ffffff;display:grid;place-items:center;font-size:22px;font-weight:900;box-shadow:0 4px 12px rgba(230,126,34,0.3);">
                {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name ?: '', 0, 1)) }}
            </div>
            <div>
                <h2 style="color:#ffffff;margin:0 0 4px;font-size:18px;font-weight:750;">{{ auth()->user()->name }}</h2>
                <div style="color:#b9ccc3;font-size:13px;display:flex;gap:14px;flex-wrap:wrap;">
                    <span><strong style="color:#ffffff;">Reg No:</strong> {{ $student?->admission_number ?? 'Pending Registration' }}</span>
                    <span>•</span>
                    <span><strong style="color:#ffffff;">Programme:</strong> {{ $student?->course?->name ?? 'Unassigned Course' }}</span>
                    @if($student?->academicSession)
                        <span>•</span>
                        <span><strong style="color:#ffffff;">Session:</strong> {{ $student->academicSession->start_date?->format('Y') }}–{{ $student->academicSession->end_date?->format('Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="display:flex;gap:10px;">
            <div style="text-align:right;background:rgba(255,255,255,0.1);padding:8px 16px;border-radius:8px;border:1px solid rgba(255,255,255,0.15);">
                <div style="font-size:11px;color:#b9ccc3;text-transform:uppercase;letter-spacing:0.5px;">Cumulative GPA</div>
                <div style="font-size:20px;font-weight:800;color:#ffffff;">{{ number_format($gpa, 2) }} <small style="font-size:12px;font-weight:400;color:#7dd3a8;">/ 4.0</small></div>
            </div>
        </div>
    </div>
</section>

<!-- 4 Key Stat Cards -->
<div class="grid4" style="margin-bottom:20px;">
    <div class="stat">
        <div class="stat-head">
            <span>Overall Performance</span>
            <i data-lucide="award" style="color:#E67E22;"></i>
        </div>
        <b class="compact-value" style="color:#0A3E50;">{{ $avgScore }}%</b>
        <small>Average assessment mark</small>
    </div>

    <div class="stat">
        <div class="stat-head">
            <span>Attendance Rate</span>
            <i data-lucide="calendar-check" style="color:#1E8449;"></i>
        </div>
        <b style="color:{{ $attendanceRate >= 75 ? '#1E8449' : '#dc2626' }};">{{ $attendanceRate }}%</b>
        <small>{{ $attendancePresent }} of {{ $attendanceTotal }} recorded sessions</small>
    </div>

    <div class="stat">
        <div class="stat-head">
            <span>Registered Units</span>
            <i data-lucide="book-open" style="color:#0A3E50;"></i>
        </div>
        <b class="compact-value">{{ $subjects->count() }}</b>
        <small>Current semester syllabus</small>
    </div>

    <div class="stat">
        <div class="stat-head">
            <span>Published Results</span>
            <i data-lucide="chart-column" style="color:#0A3E50;"></i>
        </div>
        <b>{{ $results->count() }}</b>
        <small>Graded coursework & exams</small>
    </div>
</div>

<div class="cols" style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <!-- Main Left Column: Academic Results & Units -->
    <div>
        <section class="panel" style="margin-bottom:20px;">
            <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2>Academic assessment results</h2>
                    <small style="color:var(--muted)">Continuous Assessment Tests (CATs) and Semester Examinations</small>
                </div>
                <span class="badge" style="background:#f3f4f6;color:#374151;">{{ $results->count() }} of {{ $subjects->count() }} Graded</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Unit Code & Name</th>
                            <th style="text-align:center;">CAT / Test (40)</th>
                            <th style="text-align:center;">Exam (60)</th>
                            <th style="text-align:center;">Total Score</th>
                            <th style="text-align:center;">Grade</th>
                            <th style="text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                            @php
                                $total = (float)($result->test_score + $result->exam_score);
                                $grade = $total >= 70 ? 'A' : ($total >= 60 ? 'B' : ($total >= 50 ? 'C' : ($total >= 40 ? 'D' : 'E')));
                                $isPass = $total >= 40;
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:700;color:#0A3E50;">{{ $result->subject?->code ?? 'UNIT' }}</div>
                                    <div style="font-size:13px;color:#4b5563;">{{ $result->subject?->name ?? 'Course Unit' }}</div>
                                    @if($result->subject?->staff?->user)
                                        <small style="color:#6b7280;">Lecturer: {{ $result->subject->staff->user->name }}</small>
                                    @endif
                                </td>
                                <td style="text-align:center;font-weight:600;color:#374151;">{{ $result->test_score }} / 40</td>
                                <td style="text-align:center;font-weight:600;color:#374151;">{{ $result->exam_score }} / 60</td>
                                <td style="text-align:center;" class="score {{ $total >= 50 ? 'good' : '' }}">
                                    <strong>{{ $total }}%</strong>
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge" style="background:{{ $total >= 70 ? '#e6f4ea' : ($total >= 50 ? '#f0f9ff' : '#fef2f2') }};color:{{ $total >= 70 ? '#1E8449' : ($total >= 50 ? '#0369a1' : '#dc2626') }};font-weight:800;padding:4px 10px;border-radius:6px;">
                                        {{ $grade }}
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge" style="background:{{ $isPass ? '#e6f4ea' : '#fee2e2' }};color:{{ $isPass ? '#1E8449' : '#b91c1c' }};font-weight:700;">
                                        {{ $isPass ? 'PASSED' : 'RETAKE' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty" style="padding:32px 16px;text-align:center;">
                                    <i data-lucide="file-clock" style="width:36px;height:36px;color:#9ca3af;margin-bottom:8px;"></i>
                                    <div>No results have been published for this semester yet.</div>
                                    <small style="color:#6b7280;">Grades will appear here immediately after Senate and Departmental board ratification.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Attendance Breakdown per Subject -->
        @if($subjects->isNotEmpty())
            <section class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Attendance track record</h2>
                        <small style="color:var(--muted)">Required minimum lecture attendance threshold is 75% for exam clearance</small>
                    </div>
                </div>
                <div class="panel-body" style="padding:16px 20px;">
                    <div style="display:grid;gap:14px;">
                        @foreach($subjects as $subj)
                            @php
                                $subjAtt = $subjectAttendance->get($subj->id);
                                $subTot = $subjAtt ? (int)$subjAtt->total : 0;
                                $subPres = $subjAtt ? (int)$subjAtt->present : 0;
                                $rate = $subTot > 0 ? (int)round(($subPres / $subTot) * 100) : 0;
                            @endphp
                            <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                    <div style="font-weight:700;font-size:13px;color:#0A3E50;">
                                        {{ $subj->code }} - {{ $subj->name }}
                                    </div>
                                    <div style="font-size:12px;font-weight:700;color:{{ $rate >= 75 || $subTot === 0 ? '#1E8449' : '#dc2626' }};">
                                        {{ $subTot > 0 ? $rate.'%' : 'No sessions recorded yet' }}
                                    </div>
                                </div>
                                <div style="width:100%;height:6px;background:#e2e8f0;border-radius:999px;overflow:hidden;">
                                    <div style="width:{{ max(4, $rate) }}%;height:100%;background:{{ $rate >= 75 ? '#1E8449' : ($rate >= 50 ? '#E67E22' : '#dc2626') }};border-radius:999px;"></div>
                                </div>
                                <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:11px;color:#64748b;">
                                    <span>Attended: {{ $subPres }} of {{ $subTot }} sessions</span>
                                    <span>Status: {{ $rate >= 75 || $subTot === 0 ? 'Exam Eligible' : 'Attendance Risk' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>

    <!-- Right Column: Exam Schedule, Calendar & Quick Tools -->
    <div>
        <!-- Upcoming Exam Schedule -->
        <section class="panel" style="margin-bottom:20px;">
            <div class="panel-head">
                <h2>Upcoming examinations</h2>
            </div>
            <div class="panel-body" style="padding:14px 16px;">
                @forelse($upcomingExams as $exam)
                    <div style="padding:10px 0;border-bottom:1px solid #edf2f7;display:flex;gap:12px;align-items:center;">
                        <div style="background:#e6f1eb;color:#0A3E50;border-radius:8px;padding:8px 10px;text-align:center;min-width:54px;">
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;">{{ $exam->exam_date?->format('M') ?? 'EXAM' }}</div>
                            <div style="font-size:16px;font-weight:900;">{{ $exam->exam_date?->format('d') ?? '--' }}</div>
                        </div>
                        <div>
                            <strong style="font-size:13px;color:#0A3E50;display:block;">{{ $exam->subject?->name ?? 'Subject Examination' }}</strong>
                            <small style="color:#64748b;">Slot: {{ $exam->slot ?? 'Morning' }} • Center: {{ $exam->center?->name ?? 'Main Hall' }}</small>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:18px 8px;color:#64748b;font-size:13px;">
                        <i data-lucide="calendar-days" style="width:28px;height:28px;color:#94a3b8;margin-bottom:6px;"></i>
                        <div>No upcoming exams scheduled at this time.</div>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Quick Access Card -->
        <section class="panel" style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div class="panel-head">
                <h2>Quick actions</h2>
            </div>
            <div class="panel-body" style="padding:12px 16px;display:grid;gap:8px;">
                <a href="{{ route('subjects.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;text-decoration:none;color:#0A3E50;font-weight:600;font-size:13px;">
                    <i data-lucide="book-marked" style="color:#0A3E50;width:18px;height:18px;"></i>
                    <span>Course Curriculum & Syllabus</span>
                </a>
                <a href="{{ route('account.show', 'calendar') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;text-decoration:none;color:#0A3E50;font-weight:600;font-size:13px;">
                    <i data-lucide="calendar" style="color:#1E8449;width:18px;height:18px;"></i>
                    <span>Academic Calendar & Timetable</span>
                </a>
                <a href="{{ route('account.show', 'profile') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;text-decoration:none;color:#0A3E50;font-weight:600;font-size:13px;">
                    <i data-lucide="user-check" style="color:#E67E22;width:18px;height:18px;"></i>
                    <span>My Profile & Contact Details</span>
                </a>
                <a href="{{ route('account.show', 'support') }}" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;text-decoration:none;color:#0A3E50;font-weight:600;font-size:13px;">
                    <i data-lucide="help-circle" style="color:#64748b;width:18px;height:18px;"></i>
                    <span>Student Help Desk & Support</span>
                </a>
            </div>
        </section>
    </div>
</div>
