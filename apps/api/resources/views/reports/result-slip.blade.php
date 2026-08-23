<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:10px}h1{color:#0a3e50;font-size:16px}
</style></head><body>
<h1>Mema University — Official Result Slip</h1>
<p><strong>{{ $student->person?->full_name }}</strong> · {{ $student->student_number }}</p>
<p>{{ $term->name }} · {{ $student->programme?->name }}</p>
<p>Term GPA: <strong>{{ $record->gpa }}</strong> · CGPA: <strong>{{ $record->cgpa }}</strong></p>
<p>Credits attempted: {{ $record->credits_attempted }} · Credits earned: {{ $record->credits_earned }}</p>
<p>Standing: {{ $record->academic_standing }} · Decision: {{ $record->progression_decision }}</p>
</body></html>
