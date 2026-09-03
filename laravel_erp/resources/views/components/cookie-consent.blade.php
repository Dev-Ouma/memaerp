{{-- World-Class Institutional Cookie Consent Hub (KDPA 2019 & GDPR Aligned) --}}
<div id="mema-cookie-banner" class="fixed bottom-4 left-4 right-4 md:left-6 md:right-auto md:max-w-xl z-50 bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200 shadow-2xl p-5 transition-all duration-300 transform translate-y-full opacity-0 hidden" style="box-shadow: 0 20px 40px -15px rgba(10,62,80,0.25);">
    <div class="flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-[#0A3E50]/10 text-[#0A3E50] flex items-center justify-center shrink-0">
            <i data-lucide="cookie" class="w-5 h-5 text-[#E67E22]"></i>
        </div>
        <div class="flex-1 space-y-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-extrabold text-slate-900">Cookie &amp; Privacy Choices</h3>
                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">KDPA 2019</span>
                </div>
                <button type="button" onclick="dismissCookieBanner(false)" class="text-slate-400 hover:text-slate-600 text-sm font-bold leading-none p-1" aria-label="Dismiss">&times;</button>
            </div>
            <p class="text-[11.5px] text-slate-600 leading-relaxed">
                MEMA ERP uses necessary cookies for secure student authentication and session governance. Optional telemetry cookies help optimize admissions workflows. Read our <a href="{{ route('legal.privacy') }}" class="text-[#0A3E50] font-bold underline hover:text-[#E67E22]">Privacy Policy</a> and <a href="{{ route('legal.cookies') }}" class="text-[#0A3E50] font-bold underline hover:text-[#E67E22]">Cookie Policy</a>.
            </p>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <button type="button" onclick="openCookiePreferences()" class="text-[11px] font-bold text-slate-600 hover:text-[#0A3E50] underline cursor-pointer">
            Customize Preferences
        </button>
        <div class="flex items-center gap-2 ml-auto">
            <button type="button" onclick="saveCookieChoice('essential')" class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700 font-extrabold text-[11px] transition-colors cursor-pointer">
                Reject Non-Essential
            </button>
            <button type="button" onclick="saveCookieChoice('all')" class="px-4 py-1.5 rounded-lg bg-[#0A3E50] hover:bg-[#072c39] text-white font-extrabold text-[11px] transition-all shadow-xs cursor-pointer flex items-center gap-1.5">
                <i data-lucide="check" class="w-3.5 h-3.5 text-[#E67E22]"></i> Accept All
            </button>
        </div>
    </div>
</div>

{{-- Granular Cookie Preferences Modal --}}
<div id="mema-cookie-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
        {{-- Modal Header --}}
        <div class="bg-[#0A3E50] text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="shield-check" class="w-5 h-5 text-[#E67E22]"></i>
                <h3 class="text-sm font-extrabold text-white">Institutional Cookie Governance</h3>
            </div>
            <button type="button" onclick="closeCookiePreferences()" class="text-white/70 hover:text-white text-lg font-bold">&times;</button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 overflow-y-auto space-y-4 text-xs">
            <p class="text-slate-600 text-[11.5px] leading-relaxed">
                Configure your cookie preferences below. Strictly necessary cookies cannot be disabled as they are required for ERP security, session tokens, and CSRF protection.
            </p>

            {{-- Preference 1: Strictly Necessary --}}
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start justify-between gap-3">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <strong class="font-extrabold text-slate-900 text-xs">1. Strictly Necessary</strong>
                        <span class="px-2 py-0.2 rounded text-[9px] font-bold bg-slate-200 text-slate-700">ALWAYS ACTIVE</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Session state, authentication credentials, CSRF tokens, and security firewall cookies.</p>
                </div>
                <input type="checkbox" checked disabled class="rounded text-[#0A3E50] cursor-not-allowed mt-1">
            </div>

            {{-- Preference 2: Performance & Analytics --}}
            <div class="p-3.5 rounded-xl bg-white border border-slate-200 flex items-start justify-between gap-3">
                <div class="space-y-0.5">
                    <strong class="font-extrabold text-slate-900 text-xs">2. Performance &amp; Telemetry</strong>
                    <p class="text-[11px] text-slate-500">Anonymous system latency metrics and application funnel conversion statistics.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer mt-1">
                    <input type="checkbox" id="cookie-opt-analytics" checked class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0A3E50]"></div>
                </label>
            </div>

            {{-- Preference 3: Functional Preferences --}}
            <div class="p-3.5 rounded-xl bg-white border border-slate-200 flex items-start justify-between gap-3">
                <div class="space-y-0.5">
                    <strong class="font-extrabold text-slate-900 text-xs">3. Functional Personalization</strong>
                    <p class="text-[11px] text-slate-500">Remembers selected intake terms, regional campus filters, and preferred export formats.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer mt-1">
                    <input type="checkbox" id="cookie-opt-functional" checked class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0A3E50]"></div>
                </label>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
            <a href="{{ route('legal.privacy') }}" class="text-[11px] font-bold text-[#0A3E50] underline">Read Privacy Policy</a>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeCookiePreferences()" class="px-3 py-1.5 rounded-lg text-slate-600 hover:bg-slate-200 font-bold text-xs transition-colors">Cancel</button>
                <button type="button" onclick="saveCustomCookiePreferences()" class="px-4 py-1.5 rounded-lg bg-[#0A3E50] hover:bg-[#072c39] text-white font-extrabold text-xs transition-all shadow-xs">Save Preferences</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function initCookieBanner() {
        const consentKey = 'mema_cookie_consent_v3';
        const banner = document.getElementById('mema-cookie-banner');
        
        if (!localStorage.getItem(consentKey) && banner) {
            setTimeout(() => {
                banner.classList.remove('hidden');
                setTimeout(() => {
                    banner.classList.remove('translate-y-full', 'opacity-0');
                }, 50);
            }, 800);
        }
    })();

    function saveCookieChoice(type) {
        const consentKey = 'mema_cookie_consent_v3';
        const consentData = {
            consent: type,
            timestamp: new Date().toISOString(),
            essential: true,
            analytics: type === 'all',
            functional: type === 'all'
        };
        localStorage.setItem(consentKey, JSON.stringify(consentData));
        dismissCookieBanner(true);
    }

    function saveCustomCookiePreferences() {
        const consentKey = 'mema_cookie_consent_v3';
        const analytics = document.getElementById('cookie-opt-analytics')?.checked ?? false;
        const functional = document.getElementById('cookie-opt-functional')?.checked ?? false;
        const consentData = {
            consent: 'custom',
            timestamp: new Date().toISOString(),
            essential: true,
            analytics: analytics,
            functional: functional
        };
        localStorage.setItem(consentKey, JSON.stringify(consentData));
        closeCookiePreferences();
        dismissCookieBanner(true);
    }

    function dismissCookieBanner(saved) {
        const banner = document.getElementById('mema-cookie-banner');
        if (banner) {
            banner.classList.add('translate-y-full', 'opacity-0');
            setTimeout(() => banner.classList.add('hidden'), 300);
        }
    }

    function openCookiePreferences() {
        document.getElementById('mema-cookie-modal')?.classList.remove('hidden');
    }

    function closeCookiePreferences() {
        document.getElementById('mema-cookie-modal')?.classList.add('hidden');
    }
</script>
