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
    initQuizBuilderModal(root);
    initQuizReviewModal(root);
    initInstructorSupport(root);
    initCreateCourseWizard(root);
}

function initCreateCourseWizard(root) {
    const wrap = root.querySelector('[data-create-course]');
    if (!wrap) {
        return;
    }

    // Course type cards
    const typeGroup = wrap.querySelector('[data-course-type-group]');
    if (typeGroup) {
        typeGroup.querySelectorAll('[data-course-type]').forEach((btn) => {
            btn.addEventListener('click', () => {
                typeGroup.querySelectorAll('[data-course-type]').forEach((el) => {
                    el.classList.remove('border-primary', 'bg-[#F7F0E6]');
                    el.classList.add('border-d9', 'bg-white');
                    el.querySelector('[data-type-check]')?.classList.add('hidden');
                });
                btn.classList.add('border-primary', 'bg-[#F7F0E6]');
                btn.classList.remove('border-d9', 'bg-white');
                btn.querySelector('[data-type-check]')?.classList.remove('hidden');
            });
        });
    }

    // Promo video tabs
    const promoTabs = wrap.querySelector('[data-promo-tabs]');
    if (promoTabs) {
        promoTabs.querySelectorAll('[data-promo-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                const name = tab.getAttribute('data-promo-tab');
                promoTabs.querySelectorAll('[data-promo-tab]').forEach((t) => {
                    const active = t === tab;
                    t.classList.toggle('bg-primary', active);
                    t.classList.toggle('text-white', active);
                    t.classList.toggle('bg-white', !active);
                    t.classList.toggle('text-gray', !active);
                });
                wrap.querySelectorAll('[data-promo-panel]').forEach((panel) => {
                    panel.classList.toggle('hidden', panel.getAttribute('data-promo-panel') !== name);
                });
            });
        });
    }

    // Meta description counter
    const meta = wrap.querySelector('[data-meta-desc]');
    const metaCount = wrap.querySelector('[data-meta-count]');
    if (meta && metaCount) {
        const sync = () => {
            metaCount.textContent = String(meta.value.length);
        };
        meta.addEventListener('input', sync);
        sync();
    }

    // Tags
    const tagRoot = wrap.querySelector('[data-tag-input]');
    if (tagRoot) {
        const list = tagRoot.querySelector('[data-tag-list]');
        const field = tagRoot.querySelector('[data-tag-field]');
        const addTag = (value) => {
            const text = value.trim();
            if (!text || !list) {
                return;
            }
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1.5 font-medium text-13px text-primary';
            chip.setAttribute('data-tag', '');
            chip.innerHTML = `${text}<button type="button" class="hover:opacity-70" data-tag-remove aria-label="حذف وسم"><span class="icon-[tabler--x] size-3.5"></span></button>`;
            list.appendChild(chip);
        };
        list?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-tag-remove]');
            if (btn) {
                btn.closest('[data-tag]')?.remove();
            }
        });
        field?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTag(field.value);
                field.value = '';
            }
        });
    }

    // Pricing model cards
    const pricing = wrap.querySelector('[data-pricing-model]');
    if (pricing) {
        const paidFields = pricing.querySelector('[data-paid-fields]');
        pricing.querySelectorAll('[data-price-type]').forEach((btn) => {
            btn.addEventListener('click', () => {
                pricing.querySelectorAll('[data-price-type]').forEach((el) => {
                    el.classList.remove('border-primary', 'bg-[#F7F0E6]');
                    el.classList.add('border-d9', 'bg-white');
                });
                btn.classList.add('border-primary', 'bg-[#F7F0E6]');
                btn.classList.remove('border-d9', 'bg-white');
                if (paidFields) {
                    paidFields.classList.toggle('hidden', btn.getAttribute('data-price-type') === 'free');
                }
            });
        });
    }
}

function initInstructorSupport(root) {
    const wrap = root.querySelector('[data-instructor-support]');
    if (!wrap) {
        return;
    }

    const createPanel = wrap.querySelector('[data-support-panel="create"]');
    const listPanel = wrap.querySelector('[data-support-panel="list"]');
    const viewBtns = wrap.querySelectorAll('[data-support-view]');
    const tabBtns = wrap.querySelectorAll('[data-support-tab]');
    const tabPanels = {
        tickets: wrap.querySelector('[data-support-tab-panel="tickets"]'),
        courses: wrap.querySelector('[data-support-tab-panel="courses"]'),
    };

    const setTab = (name) => {
        tabBtns.forEach((btn) => {
            const active = btn.getAttribute('data-support-tab') === name;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        Object.entries(tabPanels).forEach(([key, panel]) => {
            if (panel) {
                panel.classList.toggle('hidden', key !== name);
            }
        });
    };

    const setView = (name) => {
        if (name === 'create') {
            createPanel?.classList.remove('hidden');
            listPanel?.classList.add('hidden');
            return;
        }
        createPanel?.classList.add('hidden');
        listPanel?.classList.remove('hidden');
        setTab(name === 'courses' ? 'courses' : 'tickets');
    };

    viewBtns.forEach((btn) => {
        btn.addEventListener('click', () => setView(btn.getAttribute('data-support-view')));
    });

    tabBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            createPanel?.classList.add('hidden');
            listPanel?.classList.remove('hidden');
            setTab(btn.getAttribute('data-support-tab'));
        });
    });
}

function initQuizReviewModal(root) {
    const modal = root.querySelector('#instructor-quiz-review-modal')
        || document.querySelector('#instructor-quiz-review-modal');
    if (!modal) {
        return;
    }

    let questions = [];
    try {
        questions = JSON.parse(modal.getAttribute('data-quiz-review-questions') || '[]');
    } catch (e) {
        questions = [];
    }
    if (!questions.length) {
        return;
    }

    let index = 0;
    const els = {
        counter: modal.querySelector('[data-quiz-review-counter]'),
        question: modal.querySelector('[data-quiz-review-question]'),
        model: modal.querySelector('[data-quiz-review-model]'),
        submitted: modal.querySelector('[data-quiz-review-submitted]'),
        score: modal.querySelector('[data-quiz-review-score]'),
        max: modal.querySelector('[data-quiz-review-max]'),
        scoreInput: modal.querySelector('[data-quiz-review-score-input]'),
        edit: modal.querySelector('[data-quiz-review-edit]'),
        prev: modal.querySelector('[data-quiz-review-prev]'),
        next: modal.querySelector('[data-quiz-review-next]'),
    };

    const render = () => {
        const q = questions[index];
        if (!q) {
            return;
        }
        if (els.counter) {
            els.counter.textContent = `(${index + 1} من ${questions.length})`;
        }
        if (els.question) {
            els.question.textContent = q.question || '';
        }
        if (els.model) {
            els.model.textContent = q.model || '';
        }
        if (els.submitted) {
            els.submitted.textContent = q.submitted || '';
        }
        if (els.score) {
            els.score.textContent = q.score ?? 0;
        }
        if (els.max) {
            els.max.textContent = q.max ?? 0;
        }
        if (els.scoreInput) {
            els.scoreInput.value = q.score ?? 0;
            els.scoreInput.max = q.max ?? 0;
            els.scoreInput.classList.add('hidden');
        }
        if (els.prev) {
            els.prev.disabled = index <= 0;
        }
        if (els.next) {
            els.next.disabled = index >= questions.length - 1;
        }
    };

    els.prev?.addEventListener('click', () => {
        if (index > 0) {
            index -= 1;
            render();
        }
    });
    els.next?.addEventListener('click', () => {
        if (index < questions.length - 1) {
            index += 1;
            render();
        }
    });
    els.edit?.addEventListener('click', () => {
        if (!els.scoreInput) {
            return;
        }
        els.scoreInput.classList.toggle('hidden');
        if (!els.scoreInput.classList.contains('hidden')) {
            els.scoreInput.focus();
        }
    });
    els.scoreInput?.addEventListener('change', () => {
        const max = Number(questions[index].max ?? 0);
        let val = Number(els.scoreInput.value);
        if (Number.isNaN(val)) {
            val = 0;
        }
        val = Math.max(0, Math.min(max, val));
        questions[index].score = val;
        els.scoreInput.value = val;
        if (els.score) {
            els.score.textContent = val;
        }
    });

    render();
}

function initQuizBuilderModal(root) {
    const modal = root.querySelector('#instructor-quiz-builder-modal') || document.querySelector('#instructor-quiz-builder-modal');
    if (!modal) {
        return;
    }

    const tabs = modal.querySelectorAll('[data-quiz-builder-tab]');
    const panels = modal.querySelectorAll('[data-quiz-builder-panel]');
    const setTab = (name) => {
        tabs.forEach((tab) => {
            const active = tab.getAttribute('data-quiz-builder-tab') === name;
            tab.classList.toggle('active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.classList.toggle('bg-white', active);
            tab.classList.toggle('border-d9', active);
            tab.classList.toggle('text-primary', active);
            tab.classList.toggle('bg-[#FAF8F4]', !active);
            tab.classList.toggle('border-transparent', !active);
            tab.classList.toggle('text-gray', !active);
        });
        panels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.getAttribute('data-quiz-builder-panel') !== name);
        });
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => setTab(tab.getAttribute('data-quiz-builder-tab')));
    });

    const noTime = modal.querySelector('[data-quiz-no-time-limit]');
    const durationWrap = modal.querySelector('[data-quiz-duration-wrap]');
    if (noTime && durationWrap) {
        const syncDuration = () => {
            durationWrap.classList.toggle('hidden', noTime.checked);
        };
        noTime.addEventListener('change', syncDuration);
        syncDuration();
    }
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
