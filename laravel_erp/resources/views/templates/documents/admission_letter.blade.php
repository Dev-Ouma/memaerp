<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admission Letter - {{ $payload['application']['admission_number'] }}</title>
<style>
    @page {
        size: A4 portrait;
        margin: 16mm 20mm 16mm 20mm;
    }

    body {
        font-family: "Times New Roman", Times, Georgia, serif;
        color: #000000;
        line-height: 1.42;
        font-size: 10pt;
        background-color: #ffffff;
        margin: 0;
        padding: 0;
    }

    .crest-container {
        text-align: center;
        margin-bottom: 2px;
    }
    .crest-img {
        width: 72px;
        height: auto;
        display: inline-block;
    }

    .inst-header {
        text-align: center;
        font-family: "Times New Roman", Times, serif;
    }
    .inst-name {
        font-size: 13.5pt;
        font-weight: bold;
        letter-spacing: 0.3px;
        margin: 0;
        text-transform: uppercase;
    }
    .inst-office {
        font-size: 11.5pt;
        font-weight: bold;
        margin: 1px 0 0 0;
    }
    .inst-division {
        font-size: 11.5pt;
        font-weight: bold;
        margin: 0 0 2px 0;
    }
    .confidential-title {
        font-size: 10.5pt;
        font-weight: bold;
        letter-spacing: 0.5px;
        margin: 2px 0 10px 0;
        text-transform: uppercase;
    }

    .meta-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
        line-height: 1.35;
        margin-bottom: 16px;
    }
    .meta-table td {
        vertical-align: top;
        padding: 0;
    }

    .ref-date-block {
        font-size: 9.5pt;
        margin-bottom: 14px;
        line-height: 1.45;
    }

    .salutation {
        font-size: 9.5pt;
        margin-bottom: 10px;
    }

    .subject {
        font-size: 9.5pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    p {
        margin: 0 0 8px 0;
        text-align: justify;
    }

    .numbered-list {
        margin: 4px 0 10px 0;
        padding-left: 28px;
    }
    .numbered-list li {
        margin-bottom: 2px;
    }

    .roman-list {
        margin: 4px 0 10px 0;
        padding-left: 28px;
        list-style-type: lower-roman;
    }
    .roman-list li {
        margin-bottom: 4px;
        text-align: justify;
    }

    .section-title {
        font-weight: bold;
        text-transform: uppercase;
        font-size: 9.5pt;
        margin-top: 10px;
        margin-bottom: 2px;
    }

    .signature-area {
        margin-top: 14px;
        page-break-inside: avoid;
    }
    .sig-img {
        height: 28px;
        width: auto;
        display: block;
        margin: 4px 0 2px 0;
    }
    .sig-name {
        font-weight: bold;
        text-decoration: underline;
        font-size: 9.5pt;
        text-transform: uppercase;
    }
    .sig-title {
        font-weight: bold;
        font-size: 9.5pt;
        text-transform: uppercase;
    }
</style>
</head>
<body>

{-- Top Center Crest / Logo --}
<div class="crest-container">
    <img src="data:image/png;base64,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" class="crest-img" alt="MEMA College Crest" />
</div>

{-- Institutional Heading --}
<div class="inst-header">
    <div class="inst-name">{{ $payload['application']['institution_name'] ?? 'MEMA UNIVERSITY COLLEGE' }}</div>
    <div class="inst-office">Office of the Deputy Vice-Chancellor</div>
    <div class="inst-division">(Academic & Student Affairs)</div>
    <div class="confidential-title">CONFIDENTIAL</div>
</div>

{-- Subheader: Telegram / Telephones (Left) vs Postal / Email (Right) --}
<table class="meta-table">
    <tr>
        <td style="text-align: left; width: 50%;">
            Telegram: "MEMACOLLEGE" NAIROBI<br>
            Telephone: +254 (0) 20 2000000 / 0700 000 000<br>
            Helpdesk: support@mema.ac.ke<br>
            Admissions Hotline: +254 712 345 678
        </td>
        <td style="text-align: right; width: 50%;">
            P.O. Box 100 - 00100, GPO<br>
            NAIROBI, KENYA<br>
            Email: registrar.aca@mema.ac.ke<br>
            Website: www.mema.ac.ke
        </td>
    </tr>
</table>

{-- Reference and Date --}
<div class="ref-date-block">
    <strong>Your Ref:</strong> {{ $payload['application']['reference_number'] ?? 'MUC/ADM/2026/09/8821' }}<br><br>
    <strong>Date:</strong> {{ $payload['application']['issue_date'] ?? now()->format('jS F Y') }}
</div>

{-- Salutation and Admission Number --}
<div class="salutation">
    Dear <strong>{{ $payload['applicant']['title'] ?? 'Ms.' }} {{ strtoupper($payload['applicant']['name'] ?? $payload['applicant']['first_name']) }}</strong>, <strong>Admission Number: {{ $payload['application']['admission_number'] }}</strong>
</div>

{-- Subject Line --}
<div class="subject">
    RE: ADMISSION INTO {{ strtoupper($payload['application']['programme_title']) }} - {{ $payload['application']['academic_year'] }}
</div>

{-- Main Opening Paragraph --}
<p>
    Following your application for admission to {{ $payload['application']['institution_name'] ?? 'MEMA University College' }}, I wish to congratulate you on this achievement. You have been admitted on the basis of your qualifications, which are subject to verification by the University College. When reporting, you will be required to present original and copies of the following:
</p>

<ol class="numbered-list">
    <li>KCSE Certificate or Result Slip</li>
    <li>Birth Certificate</li>
    <li>National Identity Card or Passport</li>
    <li>Two coloured passport-size photographs</li>
    <li>Proof of payment of tuition fees</li>
</ol>

{-- Tuition Fees --}
<div class="section-title">TUITION FEES</div>
<p>
    You will pay Kshs.{{ number_format($payload['fees']['tuition_semester'] ?? 48500.00, 2) }} as tuition fee in a Semester. For more information, please contact the Finance Office at finance@mema.ac.ke or Tel: +254 700 000 000 / +254 20 2000000
</p>

{-- Fee Payment --}
<div class="section-title">FEE PAYMENT</div>
<p>
    You are required to follow the instructions below to pay the tuition fee:
</p>
<ol class="numbered-list" style="margin-top: 2px;">
    <li>While logged in to the MEMA Student Portal, navigate to the "STUDENT PAYMENT INSTRUCTIONS" section at the bottom of the page.</li>
    <li>Click on "E-Citizen / M-Pesa Payment" and follow the prompts (Paybill: {{ $payload['fees']['paybill_number'] ?? '222111' }}, Account: MEMA-{{ $payload['application']['admission_number'] }}).</li>
</ol>

{-- Commencement Date --}
<div class="section-title">COMMENCEMENT DATE</div>
<p>
    The programme will commence on {{ $payload['application']['commencement_date'] ?? '15th September 2026' }}. You are, therefore, expected to report and complete your registration on this date.
</p>

{-- Other Important Information --}
<div class="section-title">OTHER IMPORTANT INFORMATION</div>
<ol class="roman-list">
    <li>Admission to the University College does not guarantee accommodation in the Halls of Residence. Students not allocated university accommodation will be required to make private arrangements.</li>
    <li>This admission offer is subject to your adherence to the University College's Rules and Regulations.</li>
    <li>In case of any queries, please contact the Admissions Office at admissions@mema.ac.ke or Tel: +254 700 000 000 / +254 712 345 678</li>
</ol>

<p style="margin-top: 8px;">
    We look forward to welcoming you to {{ $payload['application']['institution_name'] ?? 'MEMA University College' }} and supporting you on your academic journey.
</p>

{-- Signature Block --}
<div class="signature-area">
    <p style="margin-bottom: 2px;">Yours faithfully,</p>
    <img src="data:image/png;base64,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" class="sig-img" alt="Signature" />
    <div class="sig-name">WEBUYE, H.O.D</div>
    <div class="sig-title">ACADEMIC REGISTRAR</div>
</div>

</body>
</html>
