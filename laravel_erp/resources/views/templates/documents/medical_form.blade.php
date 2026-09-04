<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Medical Examination Report - {{ $payload['application']['admission_number'] }}</title>
<style>
    @page { size: A4 portrait; margin: 15mm; }
    body { font-family: Arial, sans-serif; color: #1e293b; line-height: 1.4; font-size: 8.5pt; }
    .header { text-align: center; border-bottom: 2px solid #0A3E50; padding-bottom: 6px; margin-bottom: 12px; }
    .header h1 { font-size: 14pt; color: #0A3E50; margin: 0; font-weight: 900; }
    .header h2 { font-size: 9.5pt; color: #1E8449; margin: 2px 0 0 0; font-weight: bold; }
    .form-title { text-align: center; font-size: 10pt; font-weight: bold; background: #0A3E50; color: #ffffff; padding: 4px; margin-bottom: 12px; border-radius: 4px; }
    .section-title { font-weight: bold; color: #0A3E50; background: #f1f5f9; padding: 4px 8px; margin-top: 10px; margin-bottom: 6px; border-left: 3px solid #1E8449; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    td { padding: 5px 8px; border: 1px solid #cbd5e1; }
    .label { background: #f8fafc; font-weight: bold; color: #0A3E50; width: 28%; }
    .checkbox-item { display: inline-block; margin-right: 15px; }
</style>
</head>
<body>

<div class="header">
    <h1>MEMA UNIVERSITY COLLEGE</h1>
    <h2>UNIVERSITY HEALTH SERVICES &bull; MEDICAL EXAMINATION BOARD</h2>
    <div style="font-size: 7.5pt; color: #64748b;">Health Centre: clinic@mema.ac.ke &bull; Tel: +254 20 491 3050</div>
</div>

<div class="form-title">FORM MUC/MED/02: STUDENT ENTRANCE MEDICAL EXAMINATION CERTIFICATE</div>

<div class="section-title">PART I: STUDENT BIODATA &amp; MEDICAL HISTORY</div>
<table>
    <tr>
        <td class="label">Student Name:</td>
        <td>{{ $payload['applicant']['name'] }}</td>
        <td class="label">Admission Number:</td>
        <td style="font-weight: bold;">{{ $payload['application']['admission_number'] }}</td>
    </tr>
    <tr>
        <td class="label">Date of Birth / Gender:</td>
        <td>14/05/2004 ({{ $payload['applicant']['gender'] }})</td>
        <td class="label">Programme:</td>
        <td>{{ $payload['application']['programme_code'] }}</td>
    </tr>
</table>

<div class="section-title">PART II: CLINICAL EVALUATION (To be completed by a Registered Medical Practitioner)</div>
<table>
    <tr>
        <td class="label">Visual Acuity:</td>
        <td>Right Eye: 6/6 &nbsp;&nbsp;|&nbsp;&nbsp; Left Eye: 6/6</td>
        <td class="label">Hearing / ENT:</td>
        <td>Normal [ &nbsp; ] &nbsp;&nbsp; Impaired [ &nbsp; ]</td>
    </tr>
    <tr>
        <td class="label">Cardiovascular (BP/Pulse):</td>
        <td>BP: 120/80 mmHg &bull; Pulse: 72 bpm</td>
        <td class="label">Respiratory (Chest X-Ray):</td>
        <td>Clear lung fields / Normal</td>
    </tr>
    <tr>
        <td class="label">Blood Group / Rhesus:</td>
        <td><strong>O Positive (O+)</strong></td>
        <td class="label">Urinalysis:</td>
        <td>Normal / NAD</td>
    </tr>
    <tr>
        <td class="label">Pre-Existing Conditions / Allergies:</td>
        <td colspan="3">None recorded. Patient fit for university physical activities.</td>
    </tr>
</table>

<div class="section-title">PART III: MEDICAL OFFICER’S CERTIFICATION OF FITNESS</div>
<p>
    I certify that I have physically examined <strong>{{ $payload['applicant']['name'] }}</strong> and found him/her to be in sound mental and physical health, and <strong>FIT</strong> to undertake university degree studies.
</p>

<table style="margin-top: 15px;">
    <tr>
        <td style="width: 50%;">
            <strong>Medical Practitioner Name:</strong> Dr. David N. Wamalwa (MBChB)<br>
            <strong>License No:</strong> KMPDC # 18920<br>
            <strong>Facility:</strong> St. Luke Hospital / University Clinic<br>
            <div style="border-bottom: 1px solid #334155; margin-top: 25px;"></div>
            <div style="font-size: 7.5pt; color: #64748b;">Signature &amp; Date</div>
        </td>
        <td style="width: 50%; text-align: center; vertical-align: middle;">
            <div style="width: 110px; height: 70px; border: 2px dashed #94a3b8; display: inline-block; line-height: 70px; color: #94a3b8; font-weight: bold; font-size: 8pt;">
                OFFICIAL CLINIC STAMP
            </div>
        </td>
    </tr>
</table>

</body>
</html>
