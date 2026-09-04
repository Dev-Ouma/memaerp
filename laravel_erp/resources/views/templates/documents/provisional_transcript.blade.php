<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Provisional Academic Transcript - {{ $payload['application']['admission_number'] }}</title>
<style>
    @page { size: A4 portrait; margin: 15mm; }
    body { font-family: Arial, sans-serif; color: #1e293b; line-height: 1.4; font-size: 8.5pt; }
    .header { text-align: center; border-bottom: 2px solid #0A3E50; padding-bottom: 6px; margin-bottom: 12px; }
    .header h1 { font-size: 14pt; color: #0A3E50; margin: 0; font-weight: 900; }
    .header h2 { font-size: 9.5pt; color: #1E8449; margin: 2px 0 0 0; font-weight: bold; }
    .title-box { background: #0A3E50; color: #ffffff; padding: 5px; font-weight: bold; text-align: center; margin-bottom: 12px; border-radius: 4px; font-size: 9.5pt; }
    .student-meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt; }
    .student-meta td { padding: 4px 6px; border: 1px solid #cbd5e1; }
    .student-meta .label { background: #f8fafc; font-weight: bold; color: #0A3E50; width: 20%; }
    .transcript-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt; }
    .transcript-table th { background: #0A3E50; color: #ffffff; padding: 5px 8px; border: 1px solid #0A3E50; text-align: left; }
    .transcript-table td { padding: 5px 8px; border: 1px solid #cbd5e1; }
    .grade-scale-box { font-size: 7.5pt; color: #64748b; background: #f8fafc; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; margin-bottom: 15px; }
</style>
</head>
<body>

<div class="header">
    <h1>MEMA UNIVERSITY COLLEGE</h1>
    <h2>EXAMINATION BOARD &bull; DIRECTORATE OF ACADEMIC QUALITY</h2>
    <div style="font-size: 7.5pt; color: #64748b;">Office of the Chief Examination Officer &bull; exams@mema.ac.ke</div>
</div>

<div class="title-box">PROVISIONAL ACADEMIC PERFORMANCE TRANSCRIPT</div>

<table class="student-meta">
    <tr>
        <td class="label">Student Name:</td>
        <td style="font-weight: bold;">{{ $payload['applicant']['name'] }}</td>
        <td class="label">Admission Number:</td>
        <td style="font-weight: bold; color: #0A3E50;">{{ $payload['application']['admission_number'] }}</td>
    </tr>
    <tr>
        <td class="label">Programme:</td>
        <td>{{ $payload['application']['programme_title'] }}</td>
        <td class="label">Faculty / School:</td>
        <td>{{ $payload['application']['school_name'] }}</td>
    </tr>
    <tr>
        <td class="label">Academic Year:</td>
        <td>{{ $payload['application']['academic_year'] }}</td>
        <td class="label">Stage / Level:</td>
        <td>Year 1 Semester 1</td>
    </tr>
</table>

<table class="transcript-table">
    <thead>
        <tr>
            <th style="width: 15%;">Unit Code</th>
            <th style="width: 50%;">Course Unit Title</th>
            <th style="width: 10%; text-align: center;">Credits</th>
            <th style="width: 12%; text-align: center;">Score (%)</th>
            <th style="width: 13%; text-align: center;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payload['transcript_units'] as $unit)
        <tr>
            <td style="font-family: monospace; font-weight: bold; color: #0A3E50;">{{ $unit['code'] }}</td>
            <td>{{ $unit['title'] }}</td>
            <td style="text-align: center;">{{ number_format($unit['credits'], 1) }}</td>
            <td style="text-align: center; font-weight: bold;">{{ $unit['marks'] }}%</td>
            <td style="text-align: center; font-weight: bold; color: {{ in_array($unit['grade'], ['A', 'B+']) ? '#1E8449' : '#0A3E50' }};">{{ $unit['grade'] }}</td>
        </tr>
        @endforeach
        <tr style="background: #f1f5f9; font-weight: bold;">
            <td colspan="2">SEMESTER 1 CUMULATIVE AVERAGE / GPA</td>
            <td style="text-align: center;">17.0</td>
            <td style="text-align: center; color: #1E8449; font-size: 9pt;">75.7%</td>
            <td style="text-align: center; color: #1E8449; font-size: 9pt;">EXCELLENT (A)</td>
        </tr>
    </tbody>
</table>

<div class="grade-scale-box">
    <strong>GRADING SCALE KEY:</strong> 
    A (70–100% Excellent) &bull; 
    B (60–69% Good) &bull; 
    C (50–59% Satisfactory) &bull; 
    D (40–49% Pass) &bull; 
    E (&lt;40% Fail) &bull; 
    <em>This is a provisional transcript issued by the Examination Office subject to final ratification by the University Senate.</em>
</div>

<table style="width: 100%; margin-top: 15px;">
    <tr>
        <td style="width: 50%;">
            <div style="font-size: 8pt; color: #64748b;">Issued by:</div>
            <div style="font-weight: bold; color: #0A3E50; margin-top: 20px;">Prof. Andrew M. Mutua</div>
            <div style="font-size: 8pt; font-weight: bold;">CHIEF EXAMINATION OFFICER</div>
        </td>
        <td style="width: 50%; text-align: right;">
            <div style="font-size: 8pt; color: #64748b;">Certified by:</div>
            <div style="font-weight: bold; color: #0A3E50; margin-top: 20px;">{{ $payload['signatory']['name'] }}</div>
            <div style="font-size: 8pt; font-weight: bold;">{{ $payload['signatory']['title'] }}</div>
        </td>
    </tr>
</table>

</body>
</html>
