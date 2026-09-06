/**
 * Course-context shell helpers only.
 * Dashboard sidebar uses FlyonUI overlay drawer (data-overlay) — no custom drawer JS.
 */
export function initInstructorShell() {
    const root = document.querySelector('.panel-v1-instructor');
    if (!root) {
        return;
    }

    initDrawer({
        root,
        sidebar: '#instructor-course-sidebar',
        backdrop: '#instructor-course-sidebar-backdrop',
        openAttr: 'data-instructor-course-sidebar-toggle',
        closeAttr: 'data-instructor-course-sidebar-close',
    });

    initAccordions(root);
}

function initDrawer({ root, sidebar, backdrop, openAttr, closeAttr }) {
    const el = root.querySelector(sidebar);
    const bd = root.querySelector(backdrop);
    if (!el) {
        return;
    }

    const setOpen = (open) => {
        el.dataset.open = open ? 'true' : 'false';
        if (bd) {
            bd.classList.toggle('hidden', !open);
        }
        root.querySelectorAll(`[${openAttr}]`).forEach((btn) => {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.body.classList.toggle(
            'overflow-hidden',
            open && window.matchMedia('(max-width: 1023px)').matches
        );
    };

    root.querySelectorAll(`[${openAttr}]`).forEach((btn) => {
        btn.addEventListener('click', () => setOpen(el.dataset.open !== 'true'));
    });

    root.querySelectorAll(`[${closeAttr}]`).forEach((btn) => {
        btn.addEventListener('click', () => setOpen(false));
    });

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 1024px)').matches) {
            setOpen(false);
        }
    });
}

function initAccordions(root) {
    root.querySelectorAll('[data-course-accordion]').forEach((accordion) => {
        const toggle = accordion.querySelector('[data-course-accordion-toggle]');
        const panel = accordion.querySelector('[data-course-accordion-panel]');
        const chevron = accordion.querySelector('[data-course-accordion-chevron]');
        const subtitle = accordion.querySelector('[data-course-accordion-subtitle]');
        if (!toggle || !panel) {
            return;
        }

        toggle.addEventListener('click', () => {
            const willOpen = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            accordion.classList.toggle('bg-white', !willOpen);
            accordion.classList.toggle('bg-[#FAF8F4]', willOpen);
            accordion.classList.toggle('border-d9', !willOpen);
            accordion.classList.toggle('border-primary', willOpen);
            if (chevron) {
                chevron.classList.toggle('rotate-180', willOpen);
            }
            if (subtitle) {
                subtitle.classList.toggle('hidden', !willOpen);
            }
        });
    });
}
