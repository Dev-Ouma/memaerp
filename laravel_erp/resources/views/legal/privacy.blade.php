<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 font-quicksand">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy | MEMA University College</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; font-size: 12pt !important; }
            .print-container { max-width: 100% !important; padding: 0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased bg-slate-50">

    {{-- Top Institutional Navigation Bar --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs no-print">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admissions.catalogue') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#0A3E50] text-white flex items-center justify-center font-black text-sm shadow-xs">
                        M
                    </div>
                    <div>
                        <span class="block font-black text-slate-900 text-sm tracking-tight leading-none">MEMA UNIVERSITY</span>
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Data Privacy &amp; Governance</span>
                    </div>
                </a>
                <span class="hidden sm:inline-block text-slate-300">|</span>
                <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#0A3E50]/10 text-[#0A3E50]">
                    Privacy Policy {{ $version }}
                </span>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ url()->previous() ?: route('admissions.catalogue') }}" class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back
                </a>
                <a href="{{ route('legal.terms') }}" class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
                    Terms &amp; Conditions
                </a>
                <button type="button" onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-[#0A3E50] hover:bg-[#072c39] text-white text-xs font-extrabold transition-all shadow-xs flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-[#E67E22]"></i> Export PDF / Print
                </button>
            </div>
        </div>
    </header>

    {{-- Main Document Container --}}
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-10 shadow-xs print-container space-y-8">
            
            {{-- Document Letterhead --}}
            <div class="border-b border-slate-200 pb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider mb-1">Data Protection &amp; Confidentiality</div>
                    <h1 class="text-2xl sm:text-3xl font-black text-[#0A3E50] tracking-tight">Institutional Privacy &amp; Data Protection Policy</h1>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Compliance notice under Kenya Data Protection Act 2019 (ODPC) &amp; international privacy frameworks.</p>
                </div>
                <div class="sm:text-right text-xs text-slate-500 font-mono bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div><strong>Version:</strong> {{ $version }}</div>
                    <div><strong>Last Revised:</strong> {{ $lastUpdated }}</div>
                    <div><strong>DPO Contact:</strong> {{ $dpoEmail }}</div>
                </div>
            </div>

            {{-- Privacy Callout --}}
            <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 text-xs text-emerald-950 space-y-1.5">
                <div class="font-extrabold flex items-center gap-2 text-sm text-emerald-900">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700"></i>
                    Statutory Data Privacy Commitment
                </div>
                <p class="text-[12px] leading-relaxed text-slate-700">
                    MEMA University College is committed to safeguarding personal data entrusted to us by applicants, students, parents, and alumni. We process personal data strictly in compliance with the <strong>Kenya Data Protection Act (No. 24 of 2019)</strong> and the guidelines of the Office of the Data Protection Commissioner (ODPC).
                </p>
            </div>

            {{-- Table of Contents Quick Navigation (no-print) --}}
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs no-print">
                <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px] block mb-2">Table of Contents</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-slate-600 font-medium">
                    <a href="#priv-1" class="hover:text-[#0A3E50] hover:underline">1. Information We Collect</a>
                    <a href="#priv-2" class="hover:text-[#0A3E50] hover:underline">2. Lawful Basis for Processing</a>
                    <a href="#priv-3" class="hover:text-[#0A3E50] hover:underline">3. Purpose of Processing</a>
                    <a href="#priv-4" class="hover:text-[#0A3E50] hover:underline">4. Third-Party Disclosures</a>
                    <a href="#priv-5" class="hover:text-[#0A3E50] hover:underline">5. Data Security &amp; Encryption</a>
                    <a href="#priv-6" class="hover:text-[#0A3E50] hover:underline">6. Cookies &amp; Telemetry</a>
                    <a href="#priv-7" class="hover:text-[#0A3E50] hover:underline">7. Data Subject Rights</a>
                    <a href="#priv-8" class="hover:text-[#0A3E50] hover:underline">8. Retention Schedules</a>
                    <a href="#priv-9" class="hover:text-[#0A3E50] hover:underline">9. DPO &amp; ODPC Redress</a>
                </div>
            </div>

            {{-- Section Content --}}
            <div class="space-y-6 text-xs sm:text-[13px] text-slate-700 leading-relaxed">
                
                {{-- Section 1 --}}
                <section id="priv-1" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">1.</span> Categories of Personal Data Collected
                    </h2>
                    <p>To process admissions applications and provide educational services, MEMA University collects:</p>
                    <ul class="list-disc pl-5 space-y-1 text-slate-600 font-medium">
                        <li><strong>Identification &amp; Bio-Data:</strong> Full legal name, National ID/Passport number, Birth Certificate, Date of Birth, Gender, County, and Sub-County.</li>
                        <li><strong>Contact Details:</strong> Verified email address, mobile telephone number, physical postal address, next of kin information.</li>
                        <li><strong>Academic Credentials:</strong> KCSE index numbers, KNEC results, secondary school transcripts, prior tertiary diplomas, and certified certificates.</li>
                        <li><strong>Financial Information:</strong> M-Pesa transaction reference numbers, bank deposit receipt identifiers, and billing history.</li>
                        <li><strong>Special Needs &amp; Accommodation:</strong> Voluntary health and accessibility declarations to facilitate institutional disability support services.</li>
                    </ul>
                </section>

                {{-- Section 2 --}}
                <section id="priv-2" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">2.</span> Lawful Bases for Processing Under KDPA 2019
                    </h2>
                    <p>MEMA University processes personal data under the following statutory grounds:</p>
                    <ul class="list-disc pl-5 space-y-1 text-slate-600 font-medium">
                        <li><strong>Contractual Necessity:</strong> Assessing applications, issuing admission offers, matriculating students, and administering academic coursework.</li>
                        <li><strong>Legal &amp; Regulatory Obligations:</strong> Compliance with the Universities Act 2012, Commission for University Education (CUE) statutory reporting, and Kenya Revenue Authority (KRA) accounting.</li>
                        <li><strong>Legitimate Interests:</strong> Maintaining academic integrity, institutional analytics, fraud prevention, and campus security.</li>
                        <li><strong>Explicit Consent:</strong> Special category health data provided for disability accommodations.</li>
                    </ul>
                </section>

                {{-- Section 3 --}}
                <section id="priv-3" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">3.</span> Purpose of Data Processing
                    </h2>
                    <p>
                        Collected records are utilized solely for evaluating entry criteria, verifying credentials with KNQA/KNEC, generating student registration numbers, issuing transcripts, administering examinations, and managing nominal rolls.
                    </p>
                </section>

                {{-- Section 4 --}}
                <section id="priv-4" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">4.</span> Third-Party Data Sharing &amp; Regulatory Disclosures
                    </h2>
                    <p>We do not sell personal data. Information may only be shared with authorized institutional partners:</p>
                    <ul class="list-disc pl-5 space-y-1 text-slate-600 font-medium">
                        <li><strong>Statutory Regulators:</strong> Kenya Universities and Colleges Central Placement Service (KUCCPS), Commission for University Education (CUE), Higher Education Loans Board (HELB).</li>
                        <li><strong>Financial Service Providers:</strong> Safaricom M-Pesa (Daraja API), Kenya Commercial Bank (KCB), Stripe Payments for secure transaction processing.</li>
                        <li><strong>Examination Bodies:</strong> KNEC for qualification authenticity verifications.</li>
                    </ul>
                </section>

                {{-- Section 5 --}}
                <section id="priv-5" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">5.</span> Data Security, Encryption &amp; Infrastructure
                    </h2>
                    <p>
                        5.1 All network traffic between the applicant browser and MEMA ERP is encrypted using <strong>Transport Layer Security (TLS 1.3)</strong>.
                    </p>
                    <p>
                        5.2 Uploaded identity documents and certificates are stored in private storage nodes with <strong>AES-256 bit encryption at rest</strong>. Role-Based Access Control (RBAC) ensures only vetted admissions officers can view applicant dossiers.
                    </p>
                </section>

                {{-- Section 6 --}}
                <section id="priv-6" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">6.</span> Cookie Policy &amp; Web Telemetry
                    </h2>
                    <p>
                        MEMA ERP employs strictly necessary cookies for session tokens, CSRF validation, and authentication security. Performance and analytics cookies are optional and managed via the on-screen Cookie Consent Banner.
                    </p>
                </section>

                {{-- Section 7 --}}
                <section id="priv-7" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">7.</span> Data Subject Rights Under KDPA 2019
                    </h2>
                    <p>Under Section 26 of the Kenya Data Protection Act 2019, you have the right to:</p>
                    <ul class="list-disc pl-5 space-y-1 text-slate-600 font-medium">
                        <li><strong>Right to be Informed:</strong> Transparent knowledge of how your data is processed.</li>
                        <li><strong>Right of Access:</strong> Request a copy of all personal records held in the ERP.</li>
                        <li><strong>Right to Rectification:</strong> Correction of false or misleading information.</li>
                        <li><strong>Right to Erasure / Deletion:</strong> Deletion of unneeded data, subject to statutory graduation record keeping rules.</li>
                        <li><strong>Right to Data Portability:</strong> Obtain certified electronic copies of your academic portfolio.</li>
                    </ul>
                </section>

                {{-- Section 8 --}}
                <section id="priv-8" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">8.</span> Data Retention &amp; Archival Schedule
                    </h2>
                    <p>
                        Applicant dossiers for unsuccessful candidates are archived for 2 years following intake closure. Academic transcripts and matriculation awards for enrolled students are preserved permanently in the institutional repository as mandated by the National Archives and CUE.
                    </p>
                </section>

                {{-- Section 9 --}}
                <section id="priv-9" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">9.</span> Data Protection Officer (DPO) &amp; Complaints Redress
                    </h2>
                    <p>
                        To exercise your privacy rights or lodge a confidentiality inquiry, contact our Data Protection Officer:
                    </p>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-1">
                        <div><strong>Data Protection Officer (DPO):</strong> Office of the University Registrar</div>
                        <div><strong>Email:</strong> <a href="mailto:{{ $dpoEmail }}" class="text-[#0A3E50] font-bold underline">{{ $dpoEmail }}</a></div>
                        <div><strong>Telephone:</strong> {{ $dpoPhone }}</div>
                        <div><strong>Physical Address:</strong> University Way, Nairobi, Kenya</div>
                        <div class="text-[11px] text-slate-500 pt-1 border-t border-slate-200 mt-2">
                            If unsatisfied with our response, you have the statutory right to file a complaint with the <strong>Office of the Data Protection Commissioner (ODPC)</strong> at <a href="https://www.odpc.go.ke" target="_blank" class="text-[#0A3E50] underline font-bold">www.odpc.go.ke</a>.
                        </div>
                    </div>
                </section>
            </div>

            {{-- Institutional Seal & Signature Footer --}}
            <div class="border-t border-slate-200 pt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full border border-slate-300 flex items-center justify-center font-serif text-[#0A3E50] font-black text-xs">
                        ODPC
                    </div>
                    <div>
                        <strong class="text-slate-800 block">MEMA University Data Protection Office</strong>
                        <span>Registered Data Controller &amp; Processor • ODPC Certified</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 no-print">
                    <a href="{{ route('admissions.catalogue') }}" class="px-4 py-2 rounded-xl bg-[#0A3E50] hover:bg-[#072c39] text-white font-extrabold text-xs transition-colors shadow-xs">
                        Return to Application Portal &rarr;
                    </a>
                </div>
            </div>

        </div>
    </main>

    {{-- Global Cookies Consent Component --}}
    @include('components.cookie-consent')

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
