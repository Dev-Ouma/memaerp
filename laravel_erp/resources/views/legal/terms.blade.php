<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 font-quicksand">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms &amp; Conditions | MEMA University College</title>
    
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
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Admissions &amp; Academic Governance</span>
                    </div>
                </a>
                <span class="hidden sm:inline-block text-slate-300">|</span>
                <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#0A3E50]/10 text-[#0A3E50]">
                    Terms &amp; Conditions {{ $version }}
                </span>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ url()->previous() ?: route('admissions.catalogue') }}" class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back
                </a>
                <a href="{{ route('legal.privacy') }}" class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-colors">
                    Privacy Policy
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
                    <div class="text-xs font-bold text-[#E67E22] uppercase tracking-wider mb-1">Official University Regulations</div>
                    <h1 class="text-2xl sm:text-3xl font-black text-[#0A3E50] tracking-tight">Terms &amp; Conditions of Admission &amp; Enrolment</h1>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Governing student applicants, registered scholars, and academic portal usage.</p>
                </div>
                <div class="sm:text-right text-xs text-slate-500 font-mono bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div><strong>Version:</strong> {{ $version }}</div>
                    <div><strong>Last Revised:</strong> {{ $lastUpdated }}</div>
                    <div><strong>Jurisdiction:</strong> Republic of Kenya</div>
                </div>
            </div>

            {{-- Quick Summary Callout --}}
            <div class="p-4 rounded-xl bg-teal-50/60 border border-teal-200 text-xs text-[#0A3E50] space-y-1.5">
                <div class="font-extrabold flex items-center gap-2 text-sm">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-[#E67E22]"></i>
                    Executive Academic Declaration
                </div>
                <p class="text-[12px] leading-relaxed text-slate-700">
                    By submitting an application or accepting an offer of admission at MEMA University College, you legally certify that all personal records, KCSE/tertiary qualifications, and submitted certificates are authentic and accurate. Acceptance of these terms constitutes a binding contractual agreement between the applicant and MEMA University College under the Universities Act (No. 42 of 2012) and Commission for University Education (CUE) guidelines.
                </p>
            </div>

            {{-- Table of Contents Quick Navigation (no-print) --}}
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs no-print">
                <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px] block mb-2">Table of Contents</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-slate-600 font-medium">
                    <a href="#sec-1" class="hover:text-[#0A3E50] hover:underline">1. Eligibility &amp; Accurate Data</a>
                    <a href="#sec-2" class="hover:text-[#0A3E50] hover:underline">2. Processing Fees &amp; Payments</a>
                    <a href="#sec-3" class="hover:text-[#0A3E50] hover:underline">3. Offers &amp; Conditional Clearance</a>
                    <a href="#sec-4" class="hover:text-[#0A3E50] hover:underline">4. Matriculation &amp; Student Code</a>
                    <a href="#sec-5" class="hover:text-[#0A3E50] hover:underline">5. Academic Integrity &amp; Plagiarism</a>
                    <a href="#sec-6" class="hover:text-[#0A3E50] hover:underline">6. Intellectual Property</a>
                    <a href="#sec-7" class="hover:text-[#0A3E50] hover:underline">7. Security &amp; Acceptable Use</a>
                    <a href="#sec-8" class="hover:text-[#0A3E50] hover:underline">8. Revocation &amp; Termination</a>
                    <a href="#sec-9" class="hover:text-[#0A3E50] hover:underline">9. Governing Law of Kenya</a>
                </div>
            </div>

            {{-- Section Content --}}
            <div class="space-y-6 text-xs sm:text-[13px] text-slate-700 leading-relaxed">
                
                {{-- Section 1 --}}
                <section id="sec-1" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">1.</span> Eligibility &amp; Truthfulness of Application Data
                    </h2>
                    <p>
                        1.1 Applicants must satisfy the minimum statutory and institutional entry requirements published by the Commission for University Education (CUE) and the Kenya National Qualifications Authority (KNQA) for their respective academic programmes.
                    </p>
                    <p>
                        1.2 All information, academic transcripts, identity documents, and declarations provided through the MEMA Admissions Portal must be complete, true, and accurate. Any deliberate omission or presentation of forged certificates constitutes academic fraud, resulting in instant disqualification, permanent deregistration, and referral to law enforcement authorities under the Penal Code of Kenya (Cap 63).
                    </p>
                </section>

                {{-- Section 2 --}}
                <section id="sec-2" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">2.</span> Application Processing Fees &amp; Payment Settlements
                    </h2>
                    <p>
                        2.1 An official application processing fee of <strong>KES 1,000</strong> (or the prescribed international equivalent) is mandatory to initiate formal dossier appraisal by the University Admissions Board.
                    </p>
                    <p>
                        2.2 All financial settlements must be remitted exclusively via verified university channels:
                    </p>
                    <ul class="list-disc pl-5 space-y-1 text-slate-600 font-medium">
                        <li><strong>Safaricom M-Pesa STK Push:</strong> Live automated prompt to registered mobile wallet.</li>
                        <li><strong>KCB Paybill:</strong> Business Number <strong>522 522</strong>, Account Number <strong>0113636154</strong>.</li>
                        <li><strong>Pochi la Biashara:</strong> Mobile Wallet <strong>0113636154</strong>.</li>
                        <li><strong>Buy Goods / Till:</strong> Till Number <strong>0113636154</strong>.</li>
                        <li><strong>Stripe / Card Gateway:</strong> 3D-Secure 2.0 Visa, Mastercard, and American Express.</li>
                    </ul>
                    <p>
                        2.3 Application fees are strictly non-refundable once the admissions committee commences evaluation of candidate records.
                    </p>
                </section>

                {{-- Section 3 --}}
                <section id="sec-3" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">3.</span> Offers of Admission &amp; Conditional Clearance
                    </h2>
                    <p>
                        3.1 A formal Offer of Admission issued through the applicant dashboard is provisional and contingent upon original credential verification during physical matriculation and timely payment of first-trimester tuition fees.
                    </p>
                    <p>
                        3.2 The applicant must accept or decline the offer within <strong>14 calendar days</strong> of issuance. Unaccepted offers may be reassigned to waitlisted candidates upon deadline expiration.
                    </p>
                </section>

                {{-- Section 4 --}}
                <section id="sec-4" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">4.</span> Matriculation &amp; Student Code of Conduct
                    </h2>
                    <p>
                        4.1 Upon accepting an admission offer and completing statutory enrolment declarations, the candidate is assigned a unique Student Registration Number and admitted to full membership of the MEMA University scholarly community.
                    </p>
                    <p>
                        4.2 All registered students must abide by the Student Code of Conduct, Academic Regulations, Examination Bylaws, and Campus Safety Guidelines.
                    </p>
                </section>

                {{-- Section 5 --}}
                <section id="sec-5" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">5.</span> Academic Integrity &amp; Anti-Plagiarism Regulations
                    </h2>
                    <p>
                        5.1 The University enforces zero-tolerance towards examination malpractices, unauthorized generative AI misuse, identity impersonation, and academic plagiarism.
                    </p>
                    <p>
                        5.2 Scholarly assignments and postgraduate dissertations will be subjected to automated similarity screening engines. Violations will attract severe sanctions up to disciplinary expulsion and degree revocation.
                    </p>
                </section>

                {{-- Section 6 --}}
                <section id="sec-6" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">6.</span> Intellectual Property &amp; Educational Materials
                    </h2>
                    <p>
                        6.1 All curriculum materials, lecture modules, digital syllabi, and proprietary software provided via the MEMA LMS and Virtual Campus are intellectual property of MEMA University College.
                    </p>
                    <p>
                        6.2 Students are granted a non-exclusive, non-transferable license for personal study only. Commercial distribution, unauthorized recording, or public scraping of course content is prohibited.
                    </p>
                </section>

                {{-- Section 7 --}}
                <section id="sec-7" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">7.</span> System Security &amp; Acceptable Use
                    </h2>
                    <p>
                        7.1 Users are personally accountable for maintaining the secrecy of their ERP credentials, PIN codes, and multi-factor authentication tokens.
                    </p>
                    <p>
                        7.2 Any attempt to probe, breach, exploit, or inject malicious payloads into the MEMA ERP infrastructure will result in immediate termination of access and civil/criminal prosecution under the Computer Misuse and Cybercrimes Act of Kenya (No. 5 of 2018).
                    </p>
                </section>

                {{-- Section 8 --}}
                <section id="sec-8" class="space-y-2 pt-2">
                    <h2 class="text-base font-extrabold text-[#0A3E50] border-b border-slate-100 pb-1 flex items-center gap-2">
                        <span class="text-[#E67E22]">8.</span> Governing Law &amp; Jurisdiction
                    </h2>
                    <p>
                        These Terms and Conditions and any dispute or claim arising out of them shall be governed by and construed in accordance with the <strong>Laws of the Republic of Kenya</strong>. The Courts of Kenya shall have exclusive jurisdiction.
                    </p>
                </section>
            </div>

            {{-- Institutional Seal & Signature Footer --}}
            <div class="border-t border-slate-200 pt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full border border-slate-300 flex items-center justify-center font-serif text-[#0A3E50] font-black text-xs">
                        SEAL
                    </div>
                    <div>
                        <strong class="text-slate-800 block">Office of the Registrar (Academic Affairs)</strong>
                        <span>MEMA University College • Admissions Governance Directorate</span>
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
