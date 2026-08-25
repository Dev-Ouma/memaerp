const STORAGE_KEY = 'mema.sidebar.collapsed';
const desktopQuery = window.matchMedia('(min-width: 901px)');

function icon(name) {
    return `<i data-lucide="${name}" aria-hidden="true"></i>`;
}

function initialiseSidebar() {
    const shell = document.querySelector('.shell');
    const sidebar = document.querySelector('.sidebar');
    const brand = sidebar?.querySelector('.brand');
    const topbar = document.querySelector('.topbar');
    const profile = sidebar?.querySelector('.sidebar-profile');

    if (!shell || !sidebar || !brand || !topbar) return;

    const collapseButton = document.createElement('button');
    collapseButton.type = 'button';
    collapseButton.className = 'sidebar-collapse-toggle';
    collapseButton.setAttribute('aria-label', 'Collapse sidebar');
    brand.append(collapseButton);

    const mobileButton = document.createElement('button');
    mobileButton.type = 'button';
    mobileButton.className = 'mobile-sidebar-toggle';
    mobileButton.setAttribute('aria-label', 'Open navigation');
    mobileButton.setAttribute('aria-expanded', 'false');
    mobileButton.innerHTML = icon('menu');
    topbar.prepend(mobileButton);

    const backdrop = document.createElement('button');
    backdrop.type = 'button';
    backdrop.className = 'sidebar-backdrop';
    backdrop.setAttribute('aria-label', 'Close navigation');
    document.body.append(backdrop);

    sidebar.querySelectorAll('.nav a').forEach((item) => {
        const label = item.querySelector('span')?.textContent?.trim();
        if (label) item.dataset.sidebarLabel = label;
    });

    sidebar.querySelectorAll('.nav-group summary').forEach((item) => {
        const label = item.querySelector('span')?.textContent?.trim();
        if (label) item.dataset.sidebarLabel = label;
    });

    if (profile) {
        profile.tabIndex = 0;
        profile.setAttribute('role', 'button');
        profile.dataset.sidebarLabel = 'Account menu';
        profile.setAttribute('aria-label', 'Open account menu');
    }

    const applyCollapsedState = (collapsed) => {
        shell.classList.toggle('sidebar-collapsed', collapsed && desktopQuery.matches);
        collapseButton.innerHTML = icon(collapsed ? 'panel-left-open' : 'panel-left-close');
        collapseButton.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        collapseButton.setAttribute('aria-expanded', String(!collapsed));
        window.lucide?.createIcons();
    };

    let collapsed = localStorage.getItem(STORAGE_KEY) === 'true';
    applyCollapsedState(collapsed);

    collapseButton.addEventListener('click', () => {
        collapsed = !collapsed;
        localStorage.setItem(STORAGE_KEY, String(collapsed));
        applyCollapsedState(collapsed);
    });

    const closeMobile = () => {
        shell.classList.remove('mobile-sidebar-open');
        mobileButton.setAttribute('aria-expanded', 'false');
        mobileButton.setAttribute('aria-label', 'Open navigation');
        mobileButton.innerHTML = icon('menu');
        window.lucide?.createIcons();
    };

    mobileButton.addEventListener('click', () => {
        const open = shell.classList.toggle('mobile-sidebar-open');
        mobileButton.setAttribute('aria-expanded', String(open));
        mobileButton.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        mobileButton.innerHTML = icon(open ? 'x' : 'menu');
        window.lucide?.createIcons();
    });

    backdrop.addEventListener('click', closeMobile);
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
        if (!desktopQuery.matches) closeMobile();
    }));

    const openAccountMenu = () => {
        const accountMenu = document.querySelector('.user-menu');
        accountMenu?.setAttribute('open', '');
        accountMenu?.querySelector('summary')?.focus();
    };

    profile?.addEventListener('click', openAccountMenu);
    profile?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openAccountMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMobile();
    });

    desktopQuery.addEventListener('change', () => {
        closeMobile();
        applyCollapsedState(collapsed);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseSidebar);
} else {
    initialiseSidebar();
}
