/**
 * Admin dashboard shell — parity with instructor-shell.js
 * Handles desktop collapse (280 ↔ 80px) + mobile drawer + tooltip
 */
export function initAdminShell() {
    const root = document.querySelector('.panel-v1-admin');
    if (!root) {
        return;
    }
    initDashboardSidebarCollapse(root);
}

const SIDEBAR_STORAGE_KEY = 'panel_v1_admin_sidebar';

function initDashboardSidebarCollapse(root) {
    const sidebar = root.querySelector('#admin-layout-toggle');
    const backdrop = root.querySelector('#admin-sidebar-backdrop');
    const toggles = root.querySelectorAll('[data-admin-sidebar-toggle]');
    const closers = root.querySelectorAll('[data-admin-sidebar-close]');

    if (!toggles.length) {
        return;
    }

    if (!sidebar) {
        toggles.forEach((btn) => {
            btn.classList.add('!hidden');
        });
        return;
    }

    const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

    const syncToggleIcons = (collapsed) => {
        toggles.forEach((btn) => {
            btn.querySelector('[data-sidebar-icon="collapse"]')?.classList.toggle('hidden', collapsed);
            btn.querySelector('[data-sidebar-icon="expand"]')?.classList.toggle('hidden', !collapsed);
        });
    };

    const setDesktopCollapsed = (collapsed) => {
        root.setAttribute('data-admin-sidebar', collapsed ? 'collapsed' : 'expanded');
        try {
            localStorage.setItem(SIDEBAR_STORAGE_KEY, collapsed ? 'collapsed' : 'expanded');
        } catch (e) {}
        toggles.forEach((btn) => {
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            btn.setAttribute('aria-label', collapsed ? 'توسيع القائمة الجانبية' : 'طي القائمة الجانبية');
        });
        syncToggleIcons(collapsed);
    };

    const setMobileOpen = (open) => {
        sidebar.dataset.mobileOpen = open ? 'true' : 'false';
        if (backdrop) {
            backdrop.classList.toggle('opacity-0', !open);
            backdrop.classList.toggle('pointer-events-none', !open);
            backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
        document.body.classList.toggle('overflow-hidden', open && !isDesktop());
        toggles.forEach((btn) => {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? 'إغلاق القائمة الجانبية' : 'فتح القائمة الجانبية');
        });
        syncToggleIcons(!open);
    };

    let preferred = 'expanded';
    try {
        preferred = localStorage.getItem(SIDEBAR_STORAGE_KEY) || 'expanded';
    } catch (e) {
        preferred = 'expanded';
    }

    const applyForViewport = () => {
        if (isDesktop()) {
            setMobileOpen(false);
            setDesktopCollapsed(preferred === 'collapsed');
            return;
        }
        root.setAttribute('data-admin-sidebar', 'expanded');
        setMobileOpen(false);
    };

    applyForViewport();

    toggles.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isDesktop()) {
                preferred = root.getAttribute('data-admin-sidebar') === 'collapsed' ? 'expanded' : 'collapsed';
                setDesktopCollapsed(preferred === 'collapsed');
                return;
            }
            setMobileOpen(sidebar.dataset.mobileOpen !== 'true');
        });
    });

    closers.forEach((el) => {
        el.addEventListener('click', () => {
            if (!isDesktop()) {
                setMobileOpen(false);
            }
        });
    });

    initCollapsedTooltips(root, sidebar, isDesktop);
    initAdminGroupsAccordion(root);

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !isDesktop() && sidebar.dataset.mobileOpen === 'true') {
            setMobileOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        applyForViewport();
    });
}

function initCollapsedTooltips(root, sidebar, isDesktop) {
    let tip = document.getElementById('admin-sidebar-tooltip');
    if (!tip) {
        tip = document.createElement('div');
        tip.id = 'admin-sidebar-tooltip';
        tip.className = 'admin-sidebar-tooltip';
        tip.setAttribute('role', 'tooltip');
        document.body.appendChild(tip);
    }

    const hide = () => {
        tip.classList.remove('is-visible');
    };

    const show = (link) => {
        if (!isDesktop() || root.getAttribute('data-admin-sidebar') !== 'collapsed') {
            hide();
            return;
        }
        const label = link.getAttribute('data-tooltip') || link.getAttribute('aria-label') || '';
        if (!label) {
            hide();
            return;
        }
        tip.textContent = label;
        tip.classList.add('is-visible');
        const rect = link.getBoundingClientRect();
        const tipRect = tip.getBoundingClientRect();
        const gap = 10;
        const top = rect.top + rect.height / 2 - tipRect.height / 2;
        const isRtl = document.documentElement.getAttribute('dir') === 'rtl';
        let left;
        if (isRtl) {
            left = rect.left - tipRect.width - gap;
        } else {
            left = rect.right + gap;
        }
        tip.style.top = `${Math.max(8, top)}px`;
        tip.style.left = `${Math.max(8, left)}px`;
    };

    sidebar.querySelectorAll('.admin-nav-link[data-tooltip]').forEach((link) => {
        link.addEventListener('mouseenter', () => show(link));
        link.addEventListener('mouseleave', hide);
        link.addEventListener('focus', () => show(link));
        link.addEventListener('blur', hide);
    });

    window.addEventListener('scroll', hide, true);
}

const GROUP_STORAGE_PREFIX = 'panel_v1_admin_group:';

function initAdminGroupsAccordion(root) {
    const sidebar = root.querySelector('#admin-layout-toggle');
    if (!sidebar) return;

    const groups = sidebar.querySelectorAll('[data-admin-group]');
    if (!groups.length) return;

    const isCollapsedDesktop = () => root.getAttribute('data-admin-sidebar') === 'collapsed'
        && window.matchMedia('(min-width: 1024px)').matches;

    groups.forEach((group) => {
        const btn = group.querySelector('[data-admin-group-toggle]');
        const panel = group.querySelector('[data-admin-group-panel]');
        const chevron = group.querySelector('[data-admin-group-chevron]');
        if (!btn || !panel) return;

        const idx = group.getAttribute('data-group-index') || '0';
        const storageKey = GROUP_STORAGE_PREFIX + idx;

        // restore persisted state (default: already set via Blade: active group open, others closed)
        try {
            const saved = localStorage.getItem(storageKey);
            if (saved === 'open') {
                panel.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
                chevron?.classList.add('rotate-180');
            } else if (saved === 'closed') {
                panel.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
                chevron?.classList.remove('rotate-180');
            }
        } catch (e) {}

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            // when sidebar is collapsed to icons-only, first expand sidebar instead of toggling group
            if (isCollapsedDesktop()) {
                root.setAttribute('data-admin-sidebar', 'expanded');
                try { localStorage.setItem(SIDEBAR_STORAGE_KEY, 'expanded'); } catch (e) {}
                // also need to sync toggle icons
                root.querySelectorAll('[data-admin-sidebar-toggle]').forEach((t) => {
                    t.querySelector('[data-sidebar-icon="collapse"]')?.classList.remove('hidden');
                    t.querySelector('[data-sidebar-icon="expand"]')?.classList.add('hidden');
                });
                return;
            }

            const willOpen = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !willOpen);
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            chevron?.classList.toggle('rotate-180', willOpen);
            try { localStorage.setItem(storageKey, willOpen ? 'open' : 'closed'); } catch (e) {}
        });
    });
}


