<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
    <div>
        <div class="eyebrow">Parent and Guardian Portal</div>
        <h1 class="heading" style="margin:2px 0 4px;">Welcome back, {{ auth()->user()->first_name }}.</h1>
        <p class="sub" style="margin:0;">A clear view of your linked learner’s academic progress, attendance and term assessments.</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <span class="badge" style="background:#e6f1eb;color:#0A3E50;font-weight:700;padding:6px 12px;border-radius:20px;border:1px solid #c8ded4;font-size:12px;">
            <i data-lucide="users" style="width:14px;height:14px;margin-right:4px;vertical-align:middle;"></i>
            {{ $children->count() }} Linked {{ Str::plural('Learner', $children->count()) }}
        </span>
    </div>
</div>

@forelse($children as $child)
    @php
        $m = $childMetrics[$child->id] ?? ['avgScore' => 0, 'publishedResultsCount' => 0, 'attendanceRate' => 0, 'status' => 'Active Enrollment'];
        $results = $childResults->where('student_id', $child->id);
    @endphp
    <section class="panel" style="margin-bottom:24px;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(10,62,80,0.06);">
        <!-- Child Header Panel -->
        <div class="panel-head" style="background:#0A3E50;color:#ffffff;padding:18px 24px;border:0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div style="display:flex;align-items:center;gap:14px;">
                <span class="avatar" style="background:#E67E22;color:#ffffff;font-size:16px;font-weight:900;width:44px;height:44px;border-radius:10px;display:grid;place-items:center;box-shadow:0 3px 8px rgba(0,0,0,0.2);">
                    {{ strtoupper(substr($child->user->first_name ?: 'S', 0, 1) . substr($child->user->last_name ?: '', 0, 1)) }}
                </span>
                <div>
                    <h2 style="color:#ffffff;font-size:17px;font-weight:750;margin:0 0 2px;">{{ $child->user->name }}</h2>
                    <div style="color:#c6ded5;font-size:12px;">
                        <strong style="color:#fff;">Reg No:</strong> {{ $child->admission_number }} • 
                        <strong style="color:#fff;">Programme:</strong> {{ $child->course?->name ?? 'Unassigned Course' }}
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="badge" style="background:rgba(255,255,255,0.15);color:#ffffff;border:1px solid rgba(255,255,255,0.3);padding:5px 12px;font-weight:700;">
                    Relationship: {{ $child->pivot->relationship ?? 'Parent' }}
                </span>
                <span class="badge" style="background:#1E8449;color:#ffffff;font-weight:700;padding:5px 12px;">
                    {{ $m['status'] }}
                </span>
            </div>
        </div>

        <!-- 4 Stat Summary Cards for Child -->
        <div class="panel-body" style="padding:20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <div class="grid4">
                <div class="stat" style="background:#ffffff;">
                    <div class="stat-head">
                        <span>Attendance Health</span>
                        <i data-lucide="calendar-check" style="color:{{ $m['attendanceRate'] >= 75 ? '#1E8449' : '#dc2626' }};"></i>
                    </div>
                    <b style="color:{{ $m['attendanceRate'] >= 75 ? '#1E8449' : '#dc2626' }};">{{ $m['attendanceRate'] }}%</b>
                    <small>{{ $m['attendanceRate'] >= 75 ? 'Satisfactory attendance' : 'Below 75% threshold' }}</small>
                </div>

                <div class="stat" style="background:#ffffff;">
                    <div class="stat-head">
                        <span>Overall Academic Average</span>
                        <i data-lucide="award" style="color:#E67E22;"></i>
                    </div>
                    <b class="compact-value" style="color:#0A3E50;">{{ $m['avgScore'] }}%</b>
                    <small>Continuous & exam average</small>
                </div>

                <div class="stat" style="background:#ffffff;">
                    <div class="stat-head">
                        <span>Published Results</span>
                        <i data-lucide="book-check" style="color:#0A3E50;"></i>
                    </div>
                    <b>{{ $m['publishedResultsCount'] }}</b>
                    <small>Completed unit assessments</small>
                </div>

                <div class="stat" style="background:#ffffff;">
                    <div class="stat-head">
                        <span>Academic Session</span>
                        <i data-lucide="clock" style="color:#64748b;"></i>
                    </div>
                    <b class="compact-value" style="font-size:14px;color:#334155;">{{ $child->academicSession ? $child->academicSession->start_date?->format('Y').'–'.$child->academicSession->end_date?->format('Y') : '2025/2026' }}</b>
                    <small>Current academic year</small>
                </div>
            </div>
        </div>

        <!-- Child Academic Assessment Breakdown -->
        <div style="padding:16px 20px 8px;">
            <h3 style="font-size:14px;font-weight:700;color:#0A3E50;margin:0 0 12px;">Published assessment & grade transcript</h3>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Subject / Course Unit</th>
                        <th style="text-align:center;">CAT / Coursework (40)</th>
                        <th style="text-align:center;">Final Exam (60)</th>
                        <th style="text-align:center;">Total Score</th>
                        <th style="text-align:center;">Grade</th>
                        <th style="text-align:center;">Outcome</th>
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
                                <strong style="color:#0A3E50;">{{ $result->subject?->name ?? 'Course Unit' }}</strong>
                                <div style="font-size:11px;color:#6b7280;font-family:monospace;">{{ $result->subject?->code }}</div>
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
                            <td colspan="6" class="empty" style="padding:24px 16px;text-align:center;">
                                <i data-lucide="file-text" style="width:28px;height:28px;color:#9ca3af;margin-bottom:6px;"></i>
                                <div>No results published for this learner yet.</div>
                                <small style="color:#6b7280;">Official grades will appear here as soon as assessments are ratified by Senate.</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@empty
    <section class="panel" style="padding:48px 24px;text-align:center;">
        <i data-lucide="user-x" style="width:48px;height:48px;color:#94a3b8;margin-bottom:12px;"></i>
        <h2 style="font-size:18px;color:#0A3E50;margin:0 0 6px;">No student linked to this guardian account</h2>
        <p style="color:#64748b;max-width:480px;margin:0 auto 16px;font-size:13px;">
            If your child is enrolled at MEMA College, please contact the Registrar’s office or Admissions Desk to verify your student association.
        </p>
        <a href="{{ route('account.show', 'support') }}" class="btn" style="background:#0A3E50;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:13px;">
            <i data-lucide="help-circle" style="width:16px;height:16px;"></i> Contact Admissions Support
        </a>
    </section>
@endforelse
