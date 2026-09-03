<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apply for {{ $offering->course->name ?? 'Programme' }} | MEMA Admissions</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Quicksand', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col font-quicksand">

    {{-- Navigation Bar --}}
    <header class="bg-[#0A3E50] text-white sticky top-0 z-50 shadow-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a class="flex items-center gap-3 no-underline" href="{{ route('admissions.catalogue') }}">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#E67E22] to-[#d35400] text-white flex items-center justify-center font-extrabold text-lg shadow-sm">
                    M
                </div>
                <div>
                    <span class="block font-extrabold text-sm sm:text-base tracking-tight text-white uppercase leading-tight">MEMA UNIVERSITY COLLEGE</span>
                    <span class="block text-[11px] text-teal-200 font-medium">Undergraduate & Postgraduate Admissions</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('admissions.catalogue') }}" class="px-3.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-colors flex items-center gap-1.5 border border-white/15">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Programmes
                </a>
                <a href="{{ route('login') }}" class="px-3 py-1.5 text-xs font-bold text-teal-100 hover:text-white transition-colors">Sign in</a>
            </div>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            {{-- Left Sticky Summary Panel --}}
            <aside class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs lg:sticky lg:top-24 space-y-5">
                {{-- Course Image Banner --}}
                <div class="relative h-40 w-full overflow-hidden bg-slate-100">
                    <img src="{{ $offering->course->image_url ?? asset('images/courses/course_bcs.jpg') }}" alt="{{ $offering->course->name ?? 'Course' }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-3 left-4 right-4">
                        <span class="font-mono text-[10px] font-bold text-white bg-[#0A3E50]/90 backdrop-blur-xs px-2 py-0.5 rounded border border-white/20">
                            {{ $offering->course->code ?? 'COURSE' }}
                        </span>
                    </div>
                </div>

                <div class="px-6 pb-6 space-y-5">
                <div>
                    <div class="text-[11px] font-bold text-[#E67E22] uppercase tracking-wider">Selected Offering</div>
                    <h1 class="text-lg font-extrabold text-slate-900 mt-1 leading-snug">
                        {{ $offering->course->name ?? 'Academic Programme' }}
                    </h1>
                </div>

                <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Campus Delivery</span>
                        <span class="font-bold text-slate-800">{{ $offering->campus ?? 'Virtual Campus (ODeL)' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Study Mode</span>
                        <span class="font-bold text-slate-800">{{ $offering->study_mode ?? 'Full-time' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Academic Intake</span>
                        <span class="font-bold text-slate-800">{{ $offering->intake->name ?? 'September 2026' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500 font-medium">Application Fee</span>
                        <span class="font-extrabold text-sm text-[#0A3E50]">KES {{ number_format((float)($offering->application_fee ?? 2000)) }}</span>
                    </div>
                </div>

                <div class="bg-teal-50 border border-teal-200 rounded-xl p-4 text-xs space-y-1.5">
                    <div class="font-bold text-[#0A3E50] flex items-center gap-1.5">
                        <i data-lucide="info" class="w-4 h-4 text-[#E67E22]"></i> Application Process
                    </div>
                    <p class="text-slate-600 leading-relaxed text-[11.5px]">
                        1. Create your account & fill in bio-data.<br>
                        2. Upload KCSE/diploma transcripts.<br>
                        3. Pay application fee securely via M-Pesa.<br>
                        4. Track real-time review on your portal.
                    </p>
                </div>
                </div>
            </aside>

            {{-- Right Application Form Panel --}}
            <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs">
                
                <div class="flex justify-between items-center pb-5 border-b border-slate-200 mb-6">
                    <div>
                        <div class="text-[11px] font-bold text-[#E67E22] uppercase tracking-wider">Step 1 of 3</div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Applicant Account & Bio-Data</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Please provide accurate personal details as they appear on your national ID or passport.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i> SSL Encrypted
                    </span>
                </div>

                <form method="POST" action="{{ route('admissions.register', $offering) }}" class="space-y-5">
                    @csrf

                    @if($errors->any())
                        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs space-y-1">
                            <strong class="font-bold flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i> Please resolve the following errors:</strong>
                            <ul class="list-disc list-inside space-y-0.5 text-[11.5px]">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="first_name" class="block text-xs font-bold text-slate-700 mb-1">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                        </div>
                        <div>
                            <label for="middle_name" class="block text-xs font-bold text-slate-700 mb-1">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" autocomplete="additional-name" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                        </div>
                        <div>
                            <label for="last_name" class="block text-xs font-bold text-slate-700 mb-1">Last Name / Surname *</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="gender" class="block text-xs font-bold text-slate-700 mb-1">Gender *</label>
                            <select id="gender" name="gender" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#0A3E50]">
                                <option value="">Select Gender</option>
                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Prefer not to say" {{ old('gender') === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_of_birth" class="block text-xs font-bold text-slate-700 mb-1">Date of Birth *</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->subDay()->format('Y-m-d') }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="applicant@example.com" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">Phone Number (M-Pesa registered) *</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', '+254') }}" required autocomplete="tel" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="identity_number" class="block text-xs font-bold text-slate-700 mb-1">National ID / Passport / Birth Certificate No. *</label>
                            <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number') }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                        </div>
                        <div>
                            <label for="county" class="block text-xs font-bold text-slate-700 mb-1">County of Residence *</label>
                            <select id="county" name="county" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#0A3E50]">
                                <option value="">Select County</option>
                                @foreach(['Baringo','Bomet','Bungoma','Busia','Elgeyo-Marakwet','Embu','Garissa','Homa Bay','Isiolo','Kajiado','Kakamega','Kericho','Kiambu','Kilifi','Kirinyaga','Kisii','Kisumu','Kitui','Kwale','Laikipia','Lamu','Machakos','Makueni','Mandera','Marsabit','Meru','Migori','Mombasa','Murang’a','Nairobi','Nakuru','Nandi','Narok','Nyamira','Nyandarua','Nyeri','Samburu','Siaya','Taita-Taveta','Tana River','Tharaka-Nithi','Trans Nzoia','Turkana','Uasin Gishu','Vihiga','Wajir','West Pokot','Other / International'] as $c)
                                    <option value="{{ $c }}" {{ old('county') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 mb-1">Portal Password (min 10 characters) *</label>
                            <input type="password" id="password" name="password" minlength="10" required autocomplete="new-password" placeholder="••••••••••" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                            
                            {{-- Real-time Strength Meter --}}
                            <div class="mt-2">
                                <div class="h-1 bg-slate-200 rounded flex gap-1 overflow-hidden">
                                    <div class="flex-1 h-full bg-slate-200 transition-colors" id="apply-seg-1"></div>
                                    <div class="flex-1 h-full bg-slate-200 transition-colors" id="apply-seg-2"></div>
                                    <div class="flex-1 h-full bg-slate-200 transition-colors" id="apply-seg-3"></div>
                                    <div class="flex-1 h-full bg-slate-200 transition-colors" id="apply-seg-4"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-slate-500 font-semibold mt-1">
                                    <span id="apply-rule-len">• 10+ Chars</span>
                                    <span id="apply-rule-case">• Upper/Lower</span>
                                    <span id="apply-rule-num">• Number</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Confirm Password (Type Manually) *</label>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   minlength="10" 
                                   required 
                                   autocomplete="off" 
                                   placeholder="Type confirmation manually" 
                                   onpaste="handleApplyPasteBlocked(event)"
                                   ondrop="handleApplyPasteBlocked(event)"
                                   class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-[#0A3E50] shadow-2xs">
                            
                            {{-- Paste Blocked Notice --}}
                            <div id="apply-paste-notice" class="hidden mt-1 text-[11px] font-bold text-rose-600 bg-rose-50 p-1.5 rounded border border-rose-200">
                                ⚠️ Pasting is disabled. For security, please type your confirmation password manually.
                            </div>

                            {{-- Live Match Feedback --}}
                            <div id="apply-match-badge" class="hidden mt-1 text-[11.5px] font-bold"></div>
                        </div>
                    </div>

                    <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" name="acknowledgement" value="1" required class="mt-0.5 rounded text-[#0A3E50] focus:ring-[#0A3E50]">
                            <span class="text-slate-600 leading-snug">
                                I confirm that all information provided is accurate and verifiable against original transcripts.
                            </span>
                        </label>
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded text-[#0A3E50] focus:ring-[#0A3E50]">
                            <span class="text-slate-600 leading-snug">
                                I accept the <a href="{{ route('legal.terms') }}" target="_blank" class="text-[#0A3E50] font-bold underline hover:text-[#E67E22]">Terms &amp; Conditions</a> and <a href="{{ route('legal.privacy') }}" target="_blank" class="text-[#0A3E50] font-bold underline hover:text-[#E67E22]">Privacy Policy</a>.
                            </span>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full py-3 px-6 rounded-xl bg-[#0A3E50] hover:bg-[#072c39] text-white font-extrabold text-sm transition-all shadow-md flex items-center justify-center gap-2">
                            <span>Create Account &amp; Continue Application</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 text-[#E67E22]"></i>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200 py-6 text-xs text-slate-500 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-3">
            <div>© {{ date('Y') }} MEMA University College • Admissions Processing Engine</div>
            <div class="flex items-center gap-4 flex-wrap justify-center">
                <a href="{{ route('legal.terms') }}" target="_blank" class="font-bold text-slate-600 hover:text-[#0A3E50]">Terms &amp; Conditions</a>
                <a href="{{ route('legal.privacy') }}" target="_blank" class="font-bold text-slate-600 hover:text-[#0A3E50]">Privacy Policy</a>
                <a href="{{ route('legal.cookies') }}" target="_blank" class="font-bold text-slate-600 hover:text-[#0A3E50]">Cookies</a>
                <a href="{{ route('admissions.catalogue') }}" class="font-bold text-[#0A3E50]">Back to Catalog</a>
                <a href="{{ route('login') }}" class="font-bold text-slate-700">Existing Applicant Sign In</a>
            </div>
        </div>
    </footer>

    {{-- Interactive Security Scripts for Apply Page --}}
    <script>
        function handleApplyPasteBlocked(e) {
            e.preventDefault();
            const notice = document.getElementById('apply-paste-notice');
            if (notice) {
                notice.classList.remove('hidden');
                setTimeout(() => notice.classList.add('hidden'), 4000);
            }
            return false;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const pwd = document.getElementById('password');
            const pwdConf = document.getElementById('password_confirmation');
            const matchBadge = document.getElementById('apply-match-badge');

            const seg1 = document.getElementById('apply-seg-1');
            const seg2 = document.getElementById('apply-seg-2');
            const seg3 = document.getElementById('apply-seg-3');
            const seg4 = document.getElementById('apply-seg-4');

            const ruleLen = document.getElementById('apply-rule-len');
            const ruleCase = document.getElementById('apply-rule-case');
            const ruleNum = document.getElementById('apply-rule-num');

            function checkStrength() {
                const val = pwd?.value || '';
                const confVal = pwdConf?.value || '';

                const hasLen = val.length >= 10;
                const hasCase = /[a-z]/.test(val) && /[A-Z]/.test(val);
                const hasNum = /[0-9]/.test(val);

                if (ruleLen) ruleLen.style.color = hasLen ? '#16a34a' : '#64748b';
                if (ruleCase) ruleCase.style.color = hasCase ? '#16a34a' : '#64748b';
                if (ruleNum) ruleNum.style.color = hasNum ? '#16a34a' : '#64748b';

                let score = 0;
                if (val.length >= 8) score++;
                if (hasLen) score++;
                if (hasCase && hasNum) score++;
                if (/[^A-Za-z0-9]/.test(val) && hasLen) score++;

                seg1.style.backgroundColor = score >= 1 ? (score === 1 ? '#ef4444' : '#f59e0b') : '#e2e8f0';
                seg2.style.backgroundColor = score >= 2 ? (score === 2 ? '#f59e0b' : '#10b981') : '#e2e8f0';
                seg3.style.backgroundColor = score >= 3 ? '#10b981' : '#e2e8f0';
                seg4.style.backgroundColor = score >= 4 ? '#059669' : '#e2e8f0';

                if (confVal.length > 0) {
                    matchBadge.classList.remove('hidden');
                    if (val === confVal) {
                        matchBadge.textContent = '✓ Passwords match';
                        matchBadge.style.color = '#16a34a';
                    } else {
                        matchBadge.textContent = '✗ Passwords do not match';
                        matchBadge.style.color = '#dc2626';
                    }
                } else {
                    matchBadge.classList.add('hidden');
                }
            }

            pwd?.addEventListener('input', checkStrength);
            pwdConf?.addEventListener('input', checkStrength);
        });
    </script>

    {{-- World-Class Cookie Consent Hub --}}
    @include('components.cookie-consent')
</body>
</html>
