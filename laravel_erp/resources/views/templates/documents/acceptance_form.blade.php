<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Acceptance &amp; Declaration Form - {{ $payload['application']['admission_number'] }}</title>
<style>
    @page { size: A4 portrait; margin: 15mm; }
    body { font-family: Arial, sans-serif; color: #1e293b; line-height: 1.45; font-size: 9pt; }
    .header { text-align: center; border-bottom: 2px solid #0A3E50; padding-bottom: 8px; margin-bottom: 15px; }
    .header h1 { font-size: 15pt; color: #0A3E50; margin: 0; font-weight: 900; }
    .header h2 { font-size: 10pt; color: #1E8449; margin: 2px 0 0 0; font-weight: bold; }
    .form-title { text-align: center; font-size: 11pt; font-weight: bold; background: #0A3E50; color: #ffffff; padding: 4px 8px; margin-bottom: 14px; border-radius: 4px; text-transform: uppercase; }
    .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .grid-table td { padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 8.5pt; }
    .grid-table .label { background: #f8fafc; font-weight: bold; color: #0A3E50; width: 28%; }
    .clause-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 4px; margin-bottom: 14px; font-size: 8pt; text-align: justify; }
    .sig-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .sig-table td { width: 50%; vertical-align: top; padding: 6px; }
    .sig-line { border-bottom: 1px solid #334155; margin-top: 25px; margin-bottom: 4px; }
</style>
</head>
<body>

<div class="header">
    <h1>MEMA UNIVERSITY COLLEGE</h1>
    <h2>DIRECTORATE OF ADMISSIONS &amp; STUDENT AFFAIRS</h2>
    <div style="font-size: 8pt; color: #64748b;">P.O. Box 19500-00100 Nairobi &bull; Email: admissions@mema.ac.ke</div>
</div>

<div class="form-title">FORM MUC/ADM/01: STUDENT ACCEPTANCE &amp; DECLARATION AGREEMENT</div>

<p><strong>Instructions:</strong> Please complete and return this form on the reporting date along with supporting documents.</p>

<table class="grid-table">
    <tr>
        <td class="label">Full Legal Name:</td>
        <td>{{ $payload['applicant']['name'] }}</td>
        <td class="label">National ID / Passport:</td>
        <td>{{ $payload['applicant']['national_id'] }}</td>
    </tr>
    <tr>
        <td class="label">Admission Number:</td>
        <td style="font-weight: bold; color: #0A3E50;">{{ $payload['application']['admission_number'] }}</td>
        <td class="label">Gender / County:</td>
        <td>{{ $payload['applicant']['gender'] }} / {{ $payload['applicant']['county'] }}</td>
    </tr>
    <tr>
        <td class="label">Admitted Programme:</td>
        <td colspan="3"><strong>{{ $payload['application']['programme_title'] }}</strong> ({{ $payload['application']['programme_code'] }})</td>
    </tr>
    <tr>
        <td class="label">School / Faculty:</td>
        <td colspan="3">{{ $payload['application']['school_name'] }}</td>
    </tr>
    <tr>
        <td class="label">Next of Kin / Guardian:</td>
        <td colspan="3">{{ $payload['applicant']['next_of_kin'] }}</td>
    </tr>
</table>

<div class="clause-box">
    <strong>SECTION B: STUDENT CODE OF CONDUCT &amp; LEGAL DECLARATION</strong><br>
    I, the undersigned, hereby confirm that I accept the offer of admission to MEMA University College. I solemnly pledge to observe the University Statutes, Regulations, and Examination Rules. I understand that any false statement or presentation of forged certificates will lead to automatic expulsion and criminal prosecution.
</div>

<table class="sig-table">
    <tr>
        <td>
            <strong>Student Acceptance Signature:</strong>
            <div class="sig-line"></div>
            <div style="font-size: 8pt; color: #64748b;">Signature &amp; Date</div>
        </td>
        <td>
            <strong>Parent / Guardian / Sponsor Signature:</strong>
            <div class="sig-line"></div>
            <div style="font-size: 8pt; color: #64748b;">Signature &amp; Date</div>
        </td>
    </tr>
</table>

<div style="margin-top: 20px; border-top: 2px dashed #94a3b8; padding-top: 10px;">
    <div style="font-weight: bold; color: #0A3E50; font-size: 8.5pt;">FOR OFFICIAL REGISTRAR USE ONLY</div>
    <table class="grid-table" style="margin-top: 6px;">
        <tr>
            <td class="label">Verification Officer:</td>
            <td>___________________________</td>
            <td class="label">Decision / Seal:</td>
            <td>[ &nbsp; ] CLEARED &nbsp;&nbsp;&nbsp; [ &nbsp; ] REJECTED</td>
        </tr>
    </table>
</div>

</body>
</html>
