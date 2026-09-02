const overlay = document.createElement('div');
overlay.className = 'processing-overlay';
overlay.setAttribute('role', 'status');
overlay.setAttribute('aria-live', 'polite');
overlay.setAttribute('aria-hidden', 'true');
overlay.innerHTML = `
    <div class="processing-dialog">
        <div class="processing-loader" aria-hidden="true"><span></span><span></span><span></span></div>
        <strong data-processing-message>Processing securely…</strong>
    </div>
`;

function ensureOverlayMounted() {
    if (!document.body.contains(overlay)) {
        document.body.append(overlay);
    }
}

if (document.body) {
    ensureOverlayMounted();
} else {
    document.addEventListener('DOMContentLoaded', ensureOverlayMounted);
}

const showProcessing = (message = 'Processing securely…') => {
    ensureOverlayMounted();
    const msgEl = overlay.querySelector('[data-processing-message]');
    if (msgEl) {
        msgEl.textContent = message;
    }
    overlay.classList.add('is-visible');
    overlay.setAttribute('aria-hidden', 'false');
};

const hideProcessing = () => {
    overlay.classList.remove('is-visible');
    overlay.setAttribute('aria-hidden', 'true');
};

/* ── Contextual module loading messages ───────────────── */
const modulePatterns = [
    [/registration|admissions/, 'Loading Admissions & Registration…'],
    [/lms/, 'Loading LMS Virtual Learning…'],
    [/examination/, 'Loading Examination & Grading Board…'],
    [/fees/, 'Loading Fees Billing & Payments…'],
    [/graduation/, 'Loading Graduation & Alumni Registry…'],
    [/curriculum/, 'Loading Curriculum Setup…'],
    [/cohort/, 'Loading Cohort Setup…'],
    [/reports/, 'Loading Reports & Analytics Intelligence…'],
    [/transfers/, 'Loading Student Transfers…'],
    [/imprest/, 'Loading Imprest Management…'],
    [/service-providers/, 'Loading Service Providers & Procurement…'],
    [/budgeting/, 'Loading Budgeting & Capital Planning…'],
    [/pg-research/, 'Loading Postgraduate Research…'],
    [/task-management/, 'Loading Task Management…'],
    [/student-affairs|work-study/, 'Loading Student Affairs…'],
    [/admin-setups/, 'Loading Admin Setups…'],
    [/dashboard/, 'Loading Dashboard…'],
];

function getMessageForUrl(url, linkText) {
    if (linkText && linkText.length > 2 && linkText.length < 35 && !linkText.includes('\n')) {
        return `Loading ${linkText.trim()}…`;
    }
    for (const [pattern, msg] of modulePatterns) {
        if (pattern.test(url)) return msg;
    }
    return 'Loading MEMA ERP…';
}

/* ── Form submissions ─────────────────────────────────── */
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() === 'get' || form.dataset.noProcessing !== undefined) return;
    if (!form.checkValidity()) return;

    const submitter = event.submitter;
    const message = submitter?.dataset.processingMessage || form.dataset.processingMessage || 'Processing securely…';
    showProcessing(message);
});

/* ── Link navigation across modules ───────────────────── */
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href) return;

    // Skip anchors, javascript, external links, downloads, new tabs, and modals
    if (
        href.startsWith('#') ||
        href.startsWith('javascript') ||
        link.target === '_blank' ||
        link.hasAttribute('download') ||
        link.hasAttribute('data-modal-open') ||
        link.dataset.noProcessing !== undefined ||
        (link.hostname && link.hostname !== window.location.hostname)
    ) {
        return;
    }

    const explicitMsg = link.dataset.processingMessage;
    const linkTitle = link.querySelector('span')?.textContent || link.textContent;
    const message = explicitMsg || getMessageForUrl(href, linkTitle);

    // Smooth appearance when navigating
    showProcessing(message);
});

window.addEventListener('pageshow', hideProcessing);
window.addEventListener('load', hideProcessing);

window.MemaProcessing = { show: showProcessing, hide: hideProcessing };
