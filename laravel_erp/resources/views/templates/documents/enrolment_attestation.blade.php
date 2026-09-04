<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Certificate of Active Student Enrolment - {{ $payload['application']['admission_number'] }}</title>
<style>
    @page { size: A4 portrait; margin: 20mm 18mm; }
    body { font-family: "Times New Roman", Times, Georgia, serif; color: #0f172a; line-height: 1.6; font-size: 10.5pt; }
    .header { text-align: center; border-bottom: 3px double #0A3E50; padding-bottom: 12px; margin-bottom: 25px; }
    .header h1 { font-size: 18pt; color: #0A3E50; margin: 0; font-weight: 900; }
    .header h2 { font-size: 11pt; color: #1E8449; margin: 2px 0 0 0; }
    .cert-title { text-align: center; font-size: 13pt; font-weight: bold; text-decoration: underline; color: #0A3E50; margin: 25px 0 20px 0; text-transform: uppercase; }
    .body-text { text-align: justify; margin-bottom: 16px; text-indent: 30px; }
    .detail-table { width: 90%; margin: 15px auto; border-collapse: collapse; font-family: sans-serif; font-size: 9.5pt; }
    .detail-table td { padding: 6px 12px; border: 1px solid #cbd5e1; }
    .detail-table .label { background: #f8fafc; font-weight: bold; color: #0A3E50; width: 35%; }
    .sig-block { margin-top: 40px; }
</style>
</head>
<body>

<div class="header">
    <h1>MEMA UNIVERSITY COLLEGE</h1>
    <h2>OFFICE OF THE ACADEMIC REGISTRAR</h2>
    <div style="font-size: 8.5pt; color: #64748b; font-family: sans-serif;">P.O. Box 19500 - 00100 Nairobi &bull; Email: registrar@mema.ac.ke &bull; Web: www.mema.ac.ke</div>
</div>

<div style="text-align: right; font-family: sans-serif; font-size: 9pt; margin-bottom: 15px;">
    <strong>Date:</strong> {{ $payload['application']['issue_date'] }}<br>
    <strong>Ref:</strong> MUC/REG/ATTEST/{{ date('Y') }}/{{ substr(md5($payload['application']['admission_number']), 0, 6) }}
</div>

<div class="cert-title">TO WHOM IT MAY CONCERN: BONAFIDE STUDENT ATTESTATION</div>

<p class="body-text">
    This is to certify that <strong>{{ $payload['applicant']['name'] }}</strong> (National ID No: <strong>{{ $payload['applicant']['national_id'] }}</strong>) is a registered bonafide student of <strong>MEMA University College</strong> pursuing a full-time course of study leading to the award of the degree indicated below:
</p>

<table class="detail-table">
    <tr>
        <td class="label">Admission / Reg No:</td>
        <td style="font-weight: bold; color: #0A3E50;">{{ $payload['application']['admission_number'] }}</td>
    </tr>
    <tr>
        <td class="label">Degree Programme:</td>
        <td><strong>{{ $payload['application']['programme_title'] }}</strong></td>
    </tr>
    <tr>
        <td class="label">Faculty / School:</td>
        <td>{{ $payload['application']['school_name'] }}</td>
    </tr>
    <tr>
        <td class="label">Current Year of Study:</td>
        <td>Year 1 Semester 1 ({{ $payload['application']['academic_year'] }})</td>
    </tr>
    <tr>
        <td class="label">Mode of Study:</td>
        <td>Full-Time Regular (Campus Resident)</td>
    </tr>
    <tr>
        <td class="label">Expected Completion:</td>
        <td>December 2030 (Subject to Senate Regulations)</td>
    </tr>
</table>

<p class="body-text">
    This attestation letter is issued upon the request of the student for official verification purposes, including Higher Education Loans Board (HELB) funding, consular visa applications, or sponsor billing.
</p>

<div class="sig-block">
    <div style="font-family: 'Brush Script MT', cursive; font-size: 20pt; color: #0A3E50;">P. K. Webuye</div>
    <div style="font-weight: bold; color: #0A3E50;">{{ $payload['signatory']['name'] }}</div>
    <div style="font-size: 9pt; font-weight: bold; color: #334155;">{{ $payload['signatory']['title'] }}</div>
    <div style="font-size: 8pt; color: #64748b;">MEMA UNIVERSITY COLLEGE &bull; REGISTRAR'S SEAL</div>
</div>

</body>
</html>
