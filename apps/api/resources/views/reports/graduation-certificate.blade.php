<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;text-align:center;color:#17343f}h1{color:#0a3e50;font-size:22px;margin-top:40px}.meta{margin-top:24px;font-size:12px}
</style></head><body>
<h1>Certificate of Graduation</h1>
<p class="meta">This certifies that</p>
<h2>{{ $certificate->student?->person?->full_name }}</h2>
<p class="meta">Student No: {{ $certificate->student?->student_number }}</p>
<p class="meta">has satisfactorily completed the requirements for</p>
<h3>{{ $certificate->student?->programme?->name }}</h3>
<p class="meta">Certificate No: {{ $certificate->certificate_number }}</p>
<p class="meta">Issued: {{ $certificate->issued_at?->toFormattedDateString() }}</p>
</body></html>
