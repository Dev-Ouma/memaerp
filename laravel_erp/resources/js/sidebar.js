/*
 * Sidebar behaviour.
 *
 * The controls (collapse toggle, mobile toggle, close button, backdrop) are
 * rendered by layouts/app.blade.php rather than created here: injecting them
 * from JS meant no-JS users had no way to reach navigation on small screens,
 * and the injection caused a visible reflow on every page load.
 */

const COLLAPSE_COOKIE = 'sidebar_collapsed';
const GROUPS_KEY = 'mema.sidebar.groups';
const desktopQuery = window.matchMedia('(min-width: 901px)');
const FOCUSABLE = 'a[href], button:not([disabled]), summary, input, select, textarea, [tabindex]:not([tabindex="-1"])';

function icon(name) {
    return `<i data-lucide="${name}" aria-hidden="true"></i>`;
}

function refreshIcons() {
    window.lucide?.createIcons();
}

function persistCollapsed(value) {
    // A cookie rather than localStorage: the server reads it to render the
    // collapsed class on the first paint. One year, same-site, no sensitive data.
    document.cookie = `${COLLAPSE_COOKIE}=${value}; path=/; max-age=31536000; samesite=lax`;
}

function readGroups() {
    try {
        return new Set(JSON.parse(localStorage.getItem(GROUPS_KEY) ?? '[]'));
    } catch {
        return new Set();
    }
}

function initialiseSidebar() {
    const shell = document.querySelector('.shell');
    const sidebar = document.querySelector('.sidebar');
    if (!shell || !sidebar) return;

    const main = document.querySelector('.main');
    const nav = sidebar.querySelector('.nav');
    const collapseButton = sidebar.querySelector('.sidebar-collapse-toggle');
    const closeButton = sidebar.querySelector('.sidebar-close');
    const mobileButton = document.querySelector('.mobile-sidebar-toggle');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const profile = sidebar.querySelector('.sidebar-profile');

    /* ------------------------------------------------------ collapse */

    // The layout already rendered the persisted class from the cookie, so the
    // sidebar paints at its final width; this only syncs the control to it.
    let collapsed = shell.classList.contains('sidebar-collapsed');

    const applyCollapsedState = () => {
        const on = collapsed && desktopQuery.matches;
        shell.classList.toggle('sidebar-collapsed', on);
        if (collapseButton) {
            collapseButton.innerHTML = icon(on ? 'panel-left-open' : 'panel-left-close');
            collapseButton.setAttribute('aria-label', on ? 'Expand sidebar' : 'Collapse sidebar');
            collapseButton.setAttribute('aria-expanded', String(!on));
        }
        refreshIcons();
    };

    const setCollapsed = (value) => {
        collapsed = value;
        persistCollapsed(String(value));
        applyCollapsedState();
    };

    applyCollapsedState();
    collapseButton?.addEventListener('click', () => setCollapsed(!collapsed));

    /* ------------------------------------------------- mobile drawer */

    let lastFocused = null;

    const isOpen = () => shell.classList.contains('mobile-sidebar-open');

    const syncMobileButton = (open) => {
        if (!mobileButton) return;
        mobileButton.setAttribute('aria-expanded', String(open));
        mobileButton.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    };

    const openMobile = () => {
        if (isOpen()) return;
        lastFocused = document.activeElement;
        shell.classList.add('mobile-sidebar-open');
        document.body.classList.add('sidebar-locked');
        main?.setAttribute('inert', '');
        syncMobileButton(true);
        (sidebar.querySelector('.nav a.active') ?? closeButton ?? sidebar.querySelector(FOCUSABLE))?.focus();
    };

    const closeMobile = ({ restoreFocus = false } = {}) => {
        if (!isOpen()) {
            main?.removeAttribute('inert');
            document.body.classList.remove('sidebar-locked');
            return;
        }
        shell.classList.remove('mobile-sidebar-open');
        document.body.classList.remove('sidebar-locked');
        main?.removeAttribute('inert');
        syncMobileButton(false);
        if (restoreFocus) (lastFocused instanceof HTMLElement ? lastFocused : mobileButton)?.focus();
        lastFocused = null;
    };

    mobileButton?.addEventListener('click', () => (isOpen() ? closeMobile({ restoreFocus: true }) : openMobile()));
    closeButton?.addEventListener('click', () => closeMobile({ restoreFocus: true }));
    backdrop?.addEventListener('click', () => closeMobile({ restoreFocus: true }));

    sidebar.addEventListener('click', (event) => {
        if (event.target.closest('a') && !desktopQuery.matches) closeMobile();
    });

    // Escape closes; Tab is trapped inside the drawer while it is open, since
    // the rest of the page is inert and must not receive focus.
    sidebar.addEventListener('keydown', (event) => {
        if (!isOpen() || event.key !== 'Tab') return;
        const items = [...sidebar.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
        if (!items.length) return;
        const [first, last] = [items[0], items[items.length - 1]];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            closeMobile({ restoreFocus: true });
            return;
        }
        // Ctrl/Cmd+B is the conventional shortcut for toggling a workspace sidebar.
        if (event.key.toLowerCase() === 'b' && (event.metaKey || event.ctrlKey) && !event.altKey) {
            event.preventDefault();
            if (desktopQuery.matches) setCollapsed(!collapsed);
            else if (isOpen()) closeMobile({ restoreFocus: true });
            else openMobile();
        }
    });

    /* -------------------------------------------------- nav sections */

    const openGroups = readGroups();

    sidebar.querySelectorAll('.nav-group[data-nav-group]').forEach((group) => {
        const key = group.dataset.navGroup;
        // A group containing the current page always wins over the stored state.
        if (!group.querySelector('a.active')) group.open = openGroups.has(key);

        group.addEventListener('toggle', () => {
            const stored = readGroups();
            group.open ? stored.add(key) : stored.delete(key);
            localStorage.setItem(GROUPS_KEY, JSON.stringify([...stored]));

            // Accordion: when this group opens, close all sibling groups.
            if (group.open) {
                sidebar.querySelectorAll('.nav-group[data-nav-group]').forEach((sibling) => {
                    if (sibling !== group && sibling.open) {
                        sibling.open = false;
                        const sibKey = sibling.dataset.navGroup;
                        stored.delete(sibKey);
                    }
                });
                localStorage.setItem(GROUPS_KEY, JSON.stringify([...stored]));
            }
        });

        // Collapsed rail has nowhere to render children, so the summary
        // expands the sidebar first rather than silently doing nothing.
        group.querySelector('summary')?.addEventListener('click', (event) => {
            if (!shell.classList.contains('sidebar-collapsed')) return;
            event.preventDefault();
            setCollapsed(false);
            group.open = true;
        });
    });

    // Collapse all open nav-groups when a top-level (non-group) link is clicked.
    sidebar.querySelectorAll('.nav > ul > li > a').forEach((link) => {
        link.addEventListener('click', () => {
            sidebar.querySelectorAll('.nav-group[data-nav-group]').forEach((group) => {
                if (group.open) {
                    group.open = false;
                    const stored = readGroups();
                    stored.delete(group.dataset.navGroup);
                    localStorage.setItem(GROUPS_KEY, JSON.stringify([...stored]));
                }
            });
        });
    });

    /* ---------------------------------------------- collapsed labels */

    // A fixed-position tooltip laid out inside a scrolling container has no
    // usable static position, so the vertical offset is measured per item.
    sidebar.querySelectorAll('.nav a, .nav-group summary').forEach((item) => {
        const label = item.querySelector('span')?.textContent?.trim();
        if (label) item.dataset.sidebarLabel = label;
    });

    const positionTooltip = (event) => {
        const item = event.target.closest('[data-sidebar-label]');
        if (!item || !shell.classList.contains('sidebar-collapsed')) return;
        const rect = item.getBoundingClientRect();
        item.style.setProperty('--sidebar-tooltip-top', `${Math.round(rect.top + rect.height / 2 - 15)}px`);
    };

    sidebar.addEventListener('pointerover', positionTooltip);
    sidebar.addEventListener('focusin', positionTooltip);

    /* --------------------------------------------------- account tile */

    profile?.addEventListener('click', () => {
        const accountMenu = document.querySelector('.user-menu');
        if (!accountMenu) return;
        accountMenu.setAttribute('open', '');
        accountMenu.querySelector('summary')?.focus();
    });

    /* -------------------------------------------------- viewport sync */

    desktopQuery.addEventListener('change', () => {
        closeMobile();
        applyCollapsedState();
    });

    // Deep nav lists can push the current page below the fold.
    const active = nav?.querySelector('a.active');
    if (active && nav.scrollHeight > nav.clientHeight) {
        active.scrollIntoView({ block: 'nearest' });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseSidebar);
} else {
    initialiseSidebar();
}
