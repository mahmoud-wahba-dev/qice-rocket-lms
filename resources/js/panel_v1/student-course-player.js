export function initCoursePlayer() {
    const root = document.querySelector('.panel-v1-course-player');
    if (!root) {
        return;
    }

    initSidebarDrawer(root);
    initAccordions(root);
    initAssignmentWordCount(root);
}

function initSidebarDrawer(root) {
    const sidebar = root.querySelector('#course-player-sidebar');
    const backdrop = root.querySelector('#course-player-sidebar-backdrop');
    if (!sidebar) {
        return;
    }

    const setOpen = (open) => {
        sidebar.dataset.open = open ? 'true' : 'false';
        if (backdrop) {
            backdrop.classList.toggle('hidden', !open);
        }
        root.querySelectorAll('[data-course-sidebar-toggle]').forEach((btn) => {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.body.classList.toggle('overflow-hidden', open && window.matchMedia('(max-width: 1023px)').matches);
    };

    root.querySelectorAll('[data-course-sidebar-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            setOpen(sidebar.dataset.open !== 'true');
        });
    });

    root.querySelectorAll('[data-course-sidebar-close]').forEach((btn) => {
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

        const setOpen = (willOpen) => {
            panel.classList.toggle('hidden', !willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            accordion.dataset.open = willOpen ? 'true' : 'false';
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
        };

        toggle.addEventListener('click', () => {
            setOpen(panel.classList.contains('hidden'));
        });
    });
}

function initAssignmentWordCount(root) {
    const textarea = root.querySelector('#assignment-answer');
    const counter = root.querySelector('[data-word-count]');
    if (!textarea || !counter) {
        return;
    }

    const limit = Number(textarea.dataset.wordLimit || 500);

    const update = () => {
        const words = textarea.value.trim() ? textarea.value.trim().split(/\s+/).length : 0;
        counter.textContent = `عدد الكلمات: ${words} / ${limit}`;
    };

    textarea.addEventListener('input', update);
    update();
}
