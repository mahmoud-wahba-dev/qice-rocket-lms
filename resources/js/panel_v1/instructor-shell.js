/**
 * Course-context shell helpers only.
 * Dashboard sidebar uses FlyonUI overlay drawer (data-overlay) — no custom drawer JS.
 */
export function initInstructorShell() {
    const root = document.querySelector('.panel-v1-instructor');
    if (!root) {
        return;
    }

    initDashboardSidebarCollapse(root);

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

const SIDEBAR_STORAGE_KEY = 'panel_v1_instructor_sidebar';

function initDashboardSidebarCollapse(root) {
    const sidebar = root.querySelector('#instructor-layout-toggle');
    const backdrop = root.querySelector('#instructor-sidebar-backdrop');
    const toggles = root.querySelectorAll('[data-instructor-sidebar-toggle]');
    const closers = root.querySelectorAll('[data-instructor-sidebar-close]');

    if (!toggles.length) {
        return;
    }

    // Shared header is also used by organization layout — hide toggle there
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
        root.setAttribute('data-instructor-sidebar', collapsed ? 'collapsed' : 'expanded');
        try {
            localStorage.setItem(SIDEBAR_STORAGE_KEY, collapsed ? 'collapsed' : 'expanded');
        } catch (e) {
            // ignore
        }

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
        // On mobile keep the "expand/menu" affordance when closed
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
        // Mobile: always full labels when drawer is open; start closed
        root.setAttribute('data-instructor-sidebar', 'expanded');
        setMobileOpen(false);
    };

    applyForViewport();

    toggles.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isDesktop()) {
                preferred = root.getAttribute('data-instructor-sidebar') === 'collapsed' ? 'expanded' : 'collapsed';
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
    let tip = document.getElementById('instructor-sidebar-tooltip');
    if (!tip) {
        tip = document.createElement('div');
        tip.id = 'instructor-sidebar-tooltip';
        tip.className = 'instructor-sidebar-tooltip';
        tip.setAttribute('role', 'tooltip');
        document.body.appendChild(tip);
    }

    const hide = () => {
        tip.classList.remove('is-visible');
    };

    const show = (link) => {
        if (!isDesktop() || root.getAttribute('data-instructor-sidebar') !== 'collapsed') {
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
        // Sidebar is on inline-start (right in RTL) → tooltip toward content (inline-end / left in RTL)
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

    sidebar.querySelectorAll('.instructor-nav-link[data-tooltip]').forEach((link) => {
        link.addEventListener('mouseenter', () => show(link));
        link.addEventListener('mouseleave', hide);
        link.addEventListener('focus', () => show(link));
        link.addEventListener('blur', hide);
    });

    window.addEventListener('scroll', hide, true);
}

export function initCreateCourseWizard(root) {
    const wrap = root.querySelector('[data-create-course]');
    if (!wrap) {
        return;
    }

    const syncPartnerInstructorFields = () => {
        wrap.querySelectorAll('[data-partner-instructor]').forEach((block) => {
            const toggle = block.querySelector('[data-partner-instructor-switch]');
            const fields = block.querySelector('[data-partner-instructor-fields]');
            const picker = block.querySelector('[data-partner-picker]');
            const search = block.querySelector('[data-partner-search]');
            if (!toggle || !fields) {
                return;
            }
            const on = !!toggle.checked;
            fields.classList.toggle('hidden', !on);
            if (picker) {
                picker.classList.toggle('opacity-60', !on);
                picker.classList.toggle('pointer-events-none', !on);
                if (on) {
                    picker.removeAttribute('data-partner-picker-disabled');
                } else {
                    picker.setAttribute('data-partner-picker-disabled', '');
                }
            }
            if (search) {
                search.disabled = !on;
            }
        });
    };

    wrap.querySelectorAll('[data-partner-instructor-switch]').forEach((toggle) => {
        toggle.addEventListener('change', syncPartnerInstructorFields);
    });
    syncPartnerInstructorFields();
    initPartnerInstructorPicker(wrap);

    const isSpa = wrap.hasAttribute('data-spa-wizard');
    const form = wrap.querySelector('[data-wizard-form]');
    const storeUrl = wrap.getAttribute('data-store-url') || form?.action;
    const createUrl = wrap.getAttribute('data-create-url') || window.location.pathname;
    const stepsMeta = (() => {
        try {
            return JSON.parse(wrap.getAttribute('data-steps') || '[]');
        } catch (_) {
            return [];
        }
    })();
    const fieldLabels = {
        title: 'عنوان الدورة',
        category_id: 'التصنيف الرئيسي',
        course_type: 'نوع الدورة',
        seo_description: 'الوصف المختصر',
        description: 'الوصف التفصيلي',
        video_demo_link: 'رابط الفيديو الترويجي',
        video_demo_file: 'ملف الفيديو الترويجي',
        image_thumbnail: 'الصورة المصغرة',
        image_cover: 'غلاف الدورة',
        tags: 'الوسوم',
        quiz_id: 'الاختبار',
        certificate: 'الشهادة',
        price: 'السعر',
        capacity: 'سعة الطلاب',
        access_days: 'عدد أيام الوصول',
        confirm_rights: 'تأكيد حقوق الملكية',
        confirm_terms: 'الموافقة على الشروط',
        draft_id: 'المسودة',
    };

    let currentStep = Math.max(1, Math.min(5, parseInt(wrap.getAttribute('data-current-step') || '1', 10) || 1));
    let saving = false;
    let autosaveTimer = null;
    let dirty = false;

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || wrap.getAttribute('data-csrf')
        || '';

    const stepInfo = (num) => stepsMeta.find((s) => Number(s.num) === Number(num)) || {
        num,
        title: `الخطوة ${num}`,
        next: num < 5 ? 'التالي' : 'إرسال للمراجعة',
        progress: num * 20,
    };

    const syncTagsHidden = () => {
        const tagRoot = wrap.querySelector('[data-tag-input]');
        if (!tagRoot) {
            return;
        }
        const list = tagRoot.querySelector('[data-tag-list]');
        const tagsValue = tagRoot.querySelector('[data-tags-value]');
        if (!tagsValue) {
            return;
        }
        const texts = [];
        list?.querySelectorAll('[data-tag]').forEach((chip) => {
            const text = chip.firstChild?.textContent?.trim?.() ?? '';
            if (text) {
                texts.push(text);
            }
        });
        tagsValue.value = texts.join(',');
    };

    const setDraftId = (id) => {
        if (!id) {
            return;
        }
        wrap.setAttribute('data-draft-id', String(id));
        wrap.querySelectorAll('[data-draft-id-input]').forEach((input) => {
            input.value = String(id);
        });
        wrap.querySelector('[data-curriculum-need-draft]')?.classList.add('hidden');
        wrap.querySelector('[data-curriculum-ready]')?.classList.remove('hidden');
    };

    const setAutosaveStatus = (text) => {
        const el = wrap.querySelector('[data-autosave-status]');
        if (el) {
            el.textContent = text || '';
        }
    };

    const showErrors = (errors) => {
        const box = wrap.querySelector('[data-wizard-errors]');
        const list = wrap.querySelector('[data-wizard-errors-list]');
        if (!box || !list) {
            return;
        }
        list.innerHTML = '';
        const entries = errors && typeof errors === 'object' ? Object.entries(errors) : [];
        if (!entries.length) {
            box.classList.add('hidden');
            return;
        }
        entries.forEach(([field, messages]) => {
            const msg = Array.isArray(messages) ? messages[0] : String(messages);
            const li = document.createElement('li');
            li.className = 'font-medium text-14px text-[#B91C1C]';
            li.innerHTML = `<span class="font-bold">${escapeHtml(fieldLabels[field] || field)}:</span> ${escapeHtml(msg)}`;
            list.appendChild(li);
        });
        box.classList.remove('hidden');
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    const clearErrors = () => showErrors(null);

    const updateChrome = (step, progress, title) => {
        const info = stepInfo(step);
        const pct = progress ?? info.progress ?? step * 20;
        wrap.querySelector('[data-wizard-step-label]') && (wrap.querySelector('[data-wizard-step-label]').textContent = `الخطوة ${step} من 5`);
        wrap.querySelector('[data-wizard-step-title]') && (wrap.querySelector('[data-wizard-step-title]').textContent = info.title || '');
        wrap.querySelector('[data-wizard-footer-progress]') && (wrap.querySelector('[data-wizard-footer-progress]').textContent = `الخطوة ${step} من 5 — ${pct}% مكتمل`);
        wrap.querySelector('[data-draft-progress-pct]') && (wrap.querySelector('[data-draft-progress-pct]').textContent = String(pct));
        const bar = wrap.querySelector('[data-draft-progress-bar]');
        if (bar) {
            bar.style.width = `${pct}%`;
        }
        if (title) {
            wrap.querySelectorAll('[data-draft-title]').forEach((el) => {
                el.textContent = title;
            });
        }
        const nextLabel = wrap.querySelector('[data-wizard-next-label]');
        if (nextLabel) {
            nextLabel.textContent = info.next || (step < 5 ? 'التالي' : 'إرسال للمراجعة');
        }
        const prevBtn = wrap.querySelector('[data-wizard-prev]');
        if (prevBtn) {
            prevBtn.disabled = step <= 1;
            prevBtn.classList.toggle('opacity-60', step <= 1);
            prevBtn.classList.toggle('cursor-not-allowed', step <= 1);
        }
        wrap.querySelectorAll('[data-wizard-goto]').forEach((btn) => {
            const num = parseInt(btn.getAttribute('data-wizard-goto') || '0', 10);
            const active = num === step;
            const done = num < step;
            btn.classList.toggle('border-[#C99C69]/50', active);
            btn.classList.toggle('bg-[#F7F0E6]', active);
            btn.classList.toggle('border-d9', !active);
            btn.classList.toggle('bg-white', !active);
            const badge = btn.querySelector('[data-step-badge]');
            if (badge) {
                badge.className = `size-8 sm:size-9 shrink-0 rounded-full center font-bold text-14px sm:text-15px ${active || done ? 'bg-primary text-white' : 'bg-[#EDEDED] text-gray'}`;
                badge.innerHTML = done
                    ? '<span class="icon-[tabler--check] size-4 sm:size-5"></span>'
                    : String(num);
            }
            const label = btn.querySelector('[data-step-label]');
            if (label) {
                label.classList.toggle('text-primary', active || done);
                label.classList.toggle('text-gray', !(active || done));
            }
        });
    };

    const showStep = (step, { push = true } = {}) => {
        currentStep = Math.max(1, Math.min(5, step));
        wrap.setAttribute('data-current-step', String(currentStep));
        wrap.querySelectorAll('[data-wizard-panel]').forEach((panel) => {
            const num = parseInt(panel.getAttribute('data-wizard-panel') || '0', 10);
            panel.classList.toggle('hidden', num !== currentStep);
        });
        const stepInput = wrap.querySelector('[data-wizard-step-input]');
        if (stepInput) {
            stepInput.value = String(currentStep);
        }
        updateChrome(currentStep);
        if (push) {
            const draftId = wrap.getAttribute('data-draft-id') || '';
            const url = new URL(createUrl, window.location.origin);
            url.searchParams.set('step', String(currentStep));
            if (draftId) {
                url.searchParams.set('draft', draftId);
            }
            window.history.pushState({ step: currentStep, draft: draftId }, '', url.toString());
        }
        try {
            localStorage.setItem('panel_v1_course_wizard', JSON.stringify({
                draft: wrap.getAttribute('data-draft-id') || '',
                step: currentStep,
                at: Date.now(),
            }));
        } catch (_) {}
    };

    const buildFormData = ({ goNext, soft }) => {
        syncTagsHidden();
        syncRichEditors(wrap);
        const stepInput = wrap.querySelector('[data-wizard-step-input]');
        if (stepInput) {
            stepInput.value = String(currentStep);
        }
        const goInput = wrap.querySelector('[data-go-next-input]');
        if (goInput) {
            goInput.value = goNext;
        }
        const autoInput = wrap.querySelector('[data-autosave-input]');
        if (autoInput) {
            autoInput.value = soft ? '1' : '0';
        }
        const saveOnly = wrap.querySelector('[data-save-only-input]');
        if (saveOnly) {
            saveOnly.value = soft && goNext === 'stay' ? '1' : '0';
        }

        // Step 2 has no fields in main form — still post draft_id + wizard_step
        const fd = form ? new FormData(form) : new FormData();
        if (!form) {
            fd.append('_token', csrf());
            fd.append('wizard_step', String(currentStep));
            fd.append('draft_id', wrap.getAttribute('data-draft-id') || '');
            fd.append('go_next', goNext);
            fd.append('autosave', soft ? '1' : '0');
        }
        // Ensure current step is what we save
        fd.set('wizard_step', String(currentStep));
        fd.set('go_next', goNext);
        fd.set('autosave', soft ? '1' : '0');
        if (soft && goNext === 'stay') {
            fd.set('save_only', '1');
        } else {
            fd.set('save_only', '0');
        }
        const draftId = wrap.getAttribute('data-draft-id') || '';
        if (draftId) {
            fd.set('draft_id', draftId);
        }
        return fd;
    };

    const saveWizard = async ({ goNext = 'stay', soft = true, silent = false } = {}) => {
        if (saving) {
            return null;
        }
        // Moving into step 2+ without draft requires a real step-1 save
        if (!soft && currentStep === 1 && (goNext === 2 || goNext === '2')) {
            soft = false;
        }
        saving = true;
        setAutosaveStatus(silent ? 'جاري الحفظ...' : 'جاري الحفظ...');
        clearErrors();
        try {
            const response = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
                body: buildFormData({ goNext, soft }),
            });
            let data = null;
            try {
                data = await response.json();
            } catch (_) {
                data = null;
            }
            if (!response.ok) {
                showErrors(data?.errors || null);
                toast('خطأ في الحقل', firstValidationError(data), 'error');
                setAutosaveStatus('');
                return null;
            }
            if (data?.draft_id) {
                setDraftId(data.draft_id);
            }
            if (data?.draft_title) {
                wrap.querySelectorAll('[data-draft-title]').forEach((el) => {
                    el.textContent = data.draft_title;
                });
            }
            dirty = false;
            setAutosaveStatus(silent ? 'تم الحفظ تلقائيًا' : '');
            if (!silent) {
                toast('تم', data?.message || 'تم حفظ المسودة', 'success');
            }
            if (data?.done && data?.redirect) {
                window.location.href = data.redirect;
                return data;
            }
            return data;
        } catch (_) {
            toast('خطأ', 'فشل الاتصال بالخادم', 'error');
            setAutosaveStatus('');
            return null;
        } finally {
            saving = false;
        }
    };

    const goToStep = async (target, { soft = true, validate = false } = {}) => {
        target = Math.max(1, Math.min(5, target));
        if (target === currentStep) {
            return;
        }
        // Always persist current step before leaving (keeps refresh-safe history)
        const goingForward = target > currentStep;
        const data = await saveWizard({
            goNext: target,
            soft: soft || !goingForward || !validate,
            silent: true,
        });
        if (!data) {
            return;
        }
        showStep(target);
        if (goingForward) {
            toast('تم', 'تم حفظ التقدم', 'success');
        }
    };

    // Course type cards
    const typeGroup = wrap.querySelector('[data-course-type-group]');
    const typeValue = wrap.querySelector('[data-course-type-value]');
    const syncTypeValue = () => {
        if (!typeGroup || !typeValue) {
            return;
        }
        const active = typeGroup.querySelector('[data-course-type].border-primary')
            || typeGroup.querySelector('[data-course-type]');
        if (active) {
            typeValue.value = active.getAttribute('data-course-type') || 'recorded';
        }
    };
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
                syncTypeValue();
                dirty = true;
            });
        });
        syncTypeValue();
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
            dirty = true;
        };
        list?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-tag-remove]');
            if (btn) {
                btn.closest('[data-tag]')?.remove();
                dirty = true;
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
        const priceInput = wrap.querySelector('#wizard-price-input');
        const accessDaysWrap = pricing.querySelector('[data-access-days-wrap]');
        pricing.querySelectorAll('[data-price-type]').forEach((btn) => {
            btn.addEventListener('click', () => {
                pricing.querySelectorAll('[data-price-type]').forEach((el) => {
                    el.classList.remove('border-primary', 'bg-[#F7F0E6]');
                    el.classList.add('border-d9', 'bg-white');
                });
                btn.classList.add('border-primary', 'bg-[#F7F0E6]');
                btn.classList.remove('border-d9', 'bg-white');
                const isFree = btn.getAttribute('data-price-type') === 'free';
                if (paidFields) {
                    paidFields.classList.toggle('hidden', isFree);
                }
                if (isFree && priceInput) {
                    priceInput.value = '';
                }
                dirty = true;
            });
        });
        pricing.querySelectorAll('[data-access-duration]').forEach((radio) => {
            radio.addEventListener('change', () => {
                if (!accessDaysWrap) {
                    return;
                }
                if (radio.checked && radio.value === 'limited') {
                    accessDaysWrap.classList.remove('hidden');
                }
                if (radio.checked && radio.value === 'lifetime') {
                    accessDaysWrap.classList.add('hidden');
                }
                dirty = true;
            });
        });
    }

    if (isSpa) {
        // Prevent native form submit — everything goes through fetch
        form?.addEventListener('submit', (e) => e.preventDefault());

        wrap.querySelectorAll('[data-wizard-next]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (currentStep >= 5) {
                    const data = await saveWizard({ goNext: 'done', soft: false, silent: false });
                    return data;
                }
                await goToStep(currentStep + 1, { validate: true, soft: false });
            });
        });
        wrap.querySelectorAll('[data-wizard-prev]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                if (currentStep <= 1) {
                    return;
                }
                await goToStep(currentStep - 1, { soft: true, validate: false });
            });
        });
        wrap.querySelectorAll('[data-wizard-goto]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const target = parseInt(btn.getAttribute('data-wizard-goto') || '1', 10);
                await goToStep(target, {
                    soft: target <= currentStep,
                    validate: target > currentStep,
                });
            });
        });
        wrap.querySelectorAll('[data-wizard-save]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                await saveWizard({ goNext: 'stay', soft: true, silent: false });
            });
        });

        // Mark dirty + debounced autosave
        wrap.addEventListener('input', () => {
            dirty = true;
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(async () => {
                if (!dirty) {
                    return;
                }
                await saveWizard({ goNext: 'stay', soft: true, silent: true });
            }, 8000);
        });
        wrap.addEventListener('change', () => {
            dirty = true;
        });

        window.addEventListener('popstate', (e) => {
            const step = e.state?.step || parseInt(new URL(window.location.href).searchParams.get('step') || String(currentStep), 10);
            showStep(step, { push: false });
        });

        // Resume draft step from URL / localStorage hint
        try {
            const params = new URLSearchParams(window.location.search);
            const urlStep = parseInt(params.get('step') || String(currentStep), 10);
            if (urlStep !== currentStep) {
                showStep(urlStep, { push: false });
            } else {
                updateChrome(currentStep);
            }
            if (wrap.getAttribute('data-draft-id')) {
                setDraftId(wrap.getAttribute('data-draft-id'));
            }
        } catch (_) {
            updateChrome(currentStep);
        }

        // Soft autosave once shortly after load if there is typed content but no draft yet
        setTimeout(async () => {
            const title = form?.querySelector('[name="title"]')?.value?.trim();
            if (title && !wrap.getAttribute('data-draft-id')) {
                await saveWizard({ goNext: 'stay', soft: true, silent: true });
            }
        }, 1500);
    }

    initRichEditors(wrap);
    initCurriculumAjax(wrap);
}

/**
 * Sync contenteditable HTML into the hidden textarea before FormData submit.
 */
export function syncRichEditors(root = document) {
    root.querySelectorAll('[data-rich-editor]').forEach((editor) => {
        const content = editor.querySelector('[data-rich-content]');
        const source = editor.querySelector('[data-rich-source]');
        if (!content || !source) {
            return;
        }
        if (source.classList.contains('hidden')) {
            source.value = content.innerHTML.trim();
        } else {
            content.innerHTML = source.value;
        }
    });
}

/**
 * WYSIWYG for HTML fields (course description) — no jQuery dependency.
 */
export function initRichEditors(root = document) {
    root.querySelectorAll('[data-rich-editor]').forEach((editor) => {
        if (editor.dataset.richReady === '1') {
            return;
        }
        editor.dataset.richReady = '1';

        const content = editor.querySelector('[data-rich-content]');
        const source = editor.querySelector('[data-rich-source]');
        const toolbar = editor.querySelector('[data-rich-toolbar]');
        if (!content || !source) {
            return;
        }

        // Seed editor from textarea if contenteditable is empty
        if (!content.innerHTML.trim() && source.value.trim()) {
            content.innerHTML = source.value;
        }

        const syncToSource = () => {
            source.value = content.innerHTML.trim();
        };

        content.addEventListener('input', syncToSource);
        content.addEventListener('blur', syncToSource);

        toolbar?.querySelectorAll('[data-rich-cmd]').forEach((btn) => {
            btn.addEventListener('mousedown', (e) => e.preventDefault());
            btn.addEventListener('click', () => {
                const cmd = btn.getAttribute('data-rich-cmd');
                const val = btn.getAttribute('data-rich-value');
                content.focus();
                if (cmd === 'createLink') {
                    const url = window.prompt('أدخل الرابط:', 'https://');
                    if (url) {
                        document.execCommand('createLink', false, url);
                    }
                } else if (cmd === 'formatBlock' && val) {
                    document.execCommand('formatBlock', false, val);
                } else if (cmd) {
                    document.execCommand(cmd, false, val || null);
                }
                syncToSource();
            });
        });

        toolbar?.querySelector('[data-rich-toggle-source]')?.addEventListener('click', () => {
            const showingSource = !source.classList.contains('hidden');
            if (showingSource) {
                content.innerHTML = source.value;
                source.classList.add('hidden');
                content.classList.remove('hidden');
            } else {
                source.value = content.innerHTML.trim();
                content.classList.add('hidden');
                source.classList.remove('hidden');
                source.focus();
            }
        });

        source.addEventListener('input', () => {
            // Keep contenteditable in sync when editing HTML source
            if (!source.classList.contains('hidden')) {
                content.innerHTML = source.value;
            }
        });
    });
}

function toast(title, msg, type = 'success') {
    if (typeof window.showCartToast === 'function') {
        window.showCartToast(title, msg, type);
    }
}

function firstValidationError(data) {
    if (!data || !data.errors) {
        return data?.message || 'تعذر تنفيذ العملية';
    }
    const entries = Object.entries(data.errors);
    if (!entries.length) {
        return data.message || 'تعذر تنفيذ العملية';
    }
    const [field, messages] = entries[0];
    const msg = Array.isArray(messages) ? messages[0] : String(messages);
    // Laravel Arabic already includes the field name via :attribute
    return msg || `${field}: خطأ`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Searchable multi-select for partner instructors (chip + suggestions).
 */
function initPartnerInstructorPicker(root) {
    root.querySelectorAll('[data-partner-picker]').forEach((picker) => {
        if (picker.dataset.pickerReady === '1') {
            return;
        }
        picker.dataset.pickerReady = '1';

        const chipsEl = picker.querySelector('[data-partner-chips]');
        const hiddenEl = picker.querySelector('[data-partner-hidden]');
        const search = picker.querySelector('[data-partner-search]');
        const suggestions = picker.querySelector('[data-partner-suggestions]');
        const optionsJson = picker.querySelector('[data-partner-options]');
        if (!chipsEl || !hiddenEl || !search || !suggestions || !optionsJson) {
            return;
        }

        let options = [];
        try {
            options = JSON.parse(optionsJson.textContent || '[]');
        } catch (_) {
            options = [];
        }

        const selectedIds = () => Array.from(hiddenEl.querySelectorAll('input[name="partners[]"]'))
            .map((input) => Number(input.value))
            .filter(Boolean);

        const syncHidden = (ids) => {
            hiddenEl.innerHTML = ids.map((id) => `<input type="hidden" name="partners[]" value="${id}">`).join('');
        };

        const renderChips = (ids) => {
            chipsEl.innerHTML = '';
            ids.forEach((id) => {
                const item = options.find((opt) => Number(opt.id) === Number(id));
                if (!item) {
                    return;
                }
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1.5 max-w-full rounded-full bg-primary/10 px-3 py-1.5 font-medium text-13px text-primary';
                chip.setAttribute('data-partner-chip', '');
                chip.setAttribute('data-id', String(item.id));
                chip.innerHTML = `<span class="truncate">${escapeHtml(item.name)}</span>
                    <button type="button" class="shrink-0 hover:opacity-70" data-partner-chip-remove aria-label="إزالة المدرب">
                        <span class="icon-[tabler--x] size-3.5"></span>
                    </button>`;
                chipsEl.appendChild(chip);
            });
            syncHidden(ids);
        };

        const hideSuggestions = () => {
            suggestions.classList.add('hidden');
            suggestions.innerHTML = '';
        };

        const showSuggestions = (query) => {
            const q = String(query || '').trim().toLowerCase();
            const taken = new Set(selectedIds());
            const matches = options.filter((opt) => {
                if (taken.has(Number(opt.id))) {
                    return false;
                }
                if (!q) {
                    return true;
                }
                const hay = `${opt.name || ''} ${opt.email || ''}`.toLowerCase();
                return hay.includes(q);
            }).slice(0, 12);

            if (!matches.length) {
                suggestions.innerHTML = `<li class="px-4 py-3 font-medium text-13px text-gray">لا توجد نتائج</li>`;
                suggestions.classList.remove('hidden');
                return;
            }

            suggestions.innerHTML = matches.map((opt) => `
                <li role="option">
                    <button type="button" class="w-full text-start px-4 py-3 hover:bg-primary/5 transition"
                        data-partner-option data-id="${opt.id}">
                        <span class="block font-semibold text-14px text-primary truncate">${escapeHtml(opt.name)}</span>
                        ${opt.email ? `<span class="block font-medium text-12px text-gray truncate mt-0.5">${escapeHtml(opt.email)}</span>` : ''}
                    </button>
                </li>
            `).join('');
            suggestions.classList.remove('hidden');
        };

        const addPartner = (id) => {
            const numId = Number(id);
            if (!numId || selectedIds().includes(numId)) {
                return;
            }
            renderChips([...selectedIds(), numId]);
            search.value = '';
            hideSuggestions();
            search.focus();
        };

        chipsEl.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-partner-chip-remove]');
            if (!btn) {
                return;
            }
            const chip = btn.closest('[data-partner-chip]');
            const id = Number(chip?.getAttribute('data-id'));
            renderChips(selectedIds().filter((item) => item !== id));
        });

        suggestions.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-partner-option]');
            if (!btn) {
                return;
            }
            addPartner(btn.getAttribute('data-id'));
        });

        search.addEventListener('focus', () => {
            if (picker.hasAttribute('data-partner-picker-disabled')) {
                return;
            }
            showSuggestions(search.value);
        });

        search.addEventListener('input', () => {
            showSuggestions(search.value);
        });

        search.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hideSuggestions();
            }
            if (e.key === 'Backspace' && !search.value) {
                const ids = selectedIds();
                if (ids.length) {
                    renderChips(ids.slice(0, -1));
                }
            }
        });

        document.addEventListener('click', (e) => {
            if (!picker.contains(e.target)) {
                hideSuggestions();
            }
        });
    });
}

function lessonIcon(kind) {
    if (kind === 'text') {
        return 'icon-[tabler--file-text]';
    }
    if (kind === 'file') {
        return 'icon-[tabler--paperclip]';
    }
    return 'icon-[tabler--player-play]';
}

function buildLessonRow(draftId, lesson) {
    const row = document.createElement('div');
    row.className = 'flex flex-wrap items-center gap-3 px-4 sm:px-5 py-3.5';
    row.setAttribute('data-curriculum-lesson', String(lesson.id));
    row.setAttribute('data-lesson-kind', lesson.kind || 'session');

    const isImage = lesson.kind === 'file' && lesson.preview_kind === 'image' && lesson.preview_url;
    const thumb = isImage
        ? `<button type="button" class="size-12 rounded-10px overflow-hidden border border-d9 bg-fa shrink-0"
                data-curriculum-preview data-preview-url="${escapeHtml(lesson.preview_url)}"
                data-preview-kind="image" data-preview-title="${escapeHtml(lesson.title)}" aria-label="معاينة الملف">
                <img src="${escapeHtml(lesson.preview_url)}" alt="" class="size-full object-cover">
            </button>`
        : `<span class="size-9 rounded-8px bg-primary/10 center shrink-0">
                <span class="${lessonIcon(lesson.kind)} size-4 text-primary"></span>
            </span>`;

    const viewBtn = (lesson.kind === 'file' && lesson.view_url)
        ? `<button type="button"
                class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-8px bg-primary/10 text-primary font-semibold text-12px hover:bg-primary/15 transition"
                data-curriculum-preview
                data-preview-url="${escapeHtml(lesson.view_url)}"
                data-preview-kind="${escapeHtml(lesson.preview_kind || 'file')}"
                data-preview-title="${escapeHtml(lesson.title)}"
                aria-label="عرض الملف">
                <span class="icon-[tabler--eye] size-4"></span>
                عرض
            </button>`
        : '';

    const deleteMsg = lesson.kind === 'file'
        ? 'حذف هذا الملف من المنهج؟'
        : (lesson.kind === 'text' ? 'حذف هذا الدرس النصي؟' : 'حذف هذه الجلسة؟');

    row.innerHTML = `
        ${thumb}
        <div class="min-w-0 flex-1 text-start">
            <p class="font-semibold text-15px sm:text-16px text-primary truncate">${escapeHtml(lesson.title)}</p>
            <p class="font-medium text-13px text-gray">${escapeHtml(lesson.duration || '')}</p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            ${viewBtn}
            <button type="button" class="size-8 rounded-8px center text-red-500 hover:bg-red-50" aria-label="حذف"
                data-curriculum-delete
                data-delete-url="${escapeHtml(lesson.delete_url || '#')}"
                data-delete-title="تأكيد الحذف"
                data-delete-message="${escapeHtml(deleteMsg)}"
                data-delete-item="${escapeHtml(lesson.title)}"
                data-delete-mode="delete-lesson"
                data-delete-draft-id="${escapeHtml(draftId)}">
                <span class="icon-[tabler--trash] size-4"></span>
            </button>
        </div>`;
    return row;
}

function syncUnitLessonCount(unitEl) {
    const count = unitEl.querySelectorAll('[data-curriculum-lesson]').length;
    const countEl = unitEl.querySelector('[data-unit-lesson-count]');
    if (countEl) {
        countEl.textContent = String(count);
    }
}

function openOverlay(el) {
    if (!el) {
        return;
    }
    if (typeof window.HSOverlay !== 'undefined' && typeof window.HSOverlay.open === 'function') {
        window.HSOverlay.open(el);
        return;
    }
    el.classList.remove('hidden');
}

function closeOverlay(el) {
    if (!el) {
        return;
    }
    if (typeof window.HSOverlay !== 'undefined' && typeof window.HSOverlay.close === 'function') {
        window.HSOverlay.close(el);
        return;
    }
    el.classList.add('hidden');
}

function openCurriculumFilePreview(btn) {
    const modal = document.getElementById('curriculum-file-preview-modal');
    if (!modal || !btn) {
        return;
    }

    const url = btn.getAttribute('data-preview-url') || '';
    const kind = (btn.getAttribute('data-preview-kind') || 'file').toLowerCase();
    const title = btn.getAttribute('data-preview-title') || 'معاينة الملف';
    if (!url) {
        toast('خطأ', 'لا يوجد رابط للملف', 'error');
        return;
    }

    const titleEl = modal.querySelector('[data-preview-title]');
    const openTab = modal.querySelector('[data-preview-open-tab]');
    const body = modal.querySelector('[data-preview-body]');
    if (titleEl) {
        titleEl.textContent = title;
    }
    if (openTab) {
        openTab.setAttribute('href', url);
    }
    if (body) {
        const safeUrl = escapeHtml(url);
        const safeTitle = escapeHtml(title);
        if (kind === 'image') {
            body.innerHTML = `<img src="${safeUrl}" alt="${safeTitle}" class="max-w-full max-h-[65vh] rounded-12px object-contain shadow-sm">`;
        } else if (kind === 'video') {
            body.innerHTML = `<video src="${safeUrl}" controls class="max-w-full max-h-[65vh] rounded-12px bg-black"></video>`;
        } else if (kind === 'pdf') {
            body.innerHTML = `<iframe src="${safeUrl}" title="${safeTitle}" class="w-full h-[65vh] rounded-12px border border-d9 bg-white"></iframe>`;
        } else {
            body.innerHTML = `<div class="text-center space-y-4 py-6">
                <span class="size-16 rounded-full bg-primary/10 center mx-auto">
                    <span class="icon-[tabler--file] size-8 text-primary"></span>
                </span>
                <p class="font-semibold text-16px text-primary">${safeTitle}</p>
                <p class="font-medium text-14px text-gray">لا تتوفر معاينة مباشرة لهذا النوع — افتح الملف في تبويب جديد.</p>
                <a href="${safeUrl}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 h-11 px-5 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-90 transition">
                    <span class="icon-[tabler--external-link] size-4"></span>
                    فتح الملف
                </a>
            </div>`;
        }
    }

    openOverlay(modal);
}

function initCurriculumAjax(wrap) {
    const root = wrap.querySelector('[data-curriculum-root]');
    if (!root) {
        return;
    }

    const draftId = wrap.getAttribute('data-draft-id') || '';
    const unitsWrap = root.querySelector('[data-curriculum-units]');
    const unitTemplate = document.getElementById('curriculum-unit-template');
    const deleteModal = document.getElementById('instructor-confirm-delete-modal');
    const deleteForm = document.getElementById('instructor-confirm-delete-form');
    const deleteTitle = document.getElementById('instructor-confirm-delete-title');
    const deleteMessage = document.getElementById('instructor-confirm-delete-message');
    const deleteItem = document.getElementById('instructor-confirm-delete-item');
    let pendingDelete = null;

    const setBusy = (form, busy) => {
        const btn = form.querySelector('[type="submit"]');
        if (!btn) {
            return;
        }
        btn.disabled = busy;
        btn.classList.toggle('opacity-70', busy);
    };

    const applyDeleteResult = (mode, data) => {
        if (mode === 'delete-chapter' && data?.deleted) {
            root.querySelector(`[data-curriculum-unit="${data.deleted.id}"]`)?.remove();
            if (unitsWrap && !unitsWrap.querySelector('[data-curriculum-unit]')) {
                unitsWrap.innerHTML = `<div class="rounded-14px border border-dashed border-d9 px-6 py-10 center flex-col text-center" data-curriculum-empty>
                    <p class="font-semibold text-18px text-gray">لا توجد وحدات بعد</p>
                    <p class="font-medium text-14px text-gray mt-2">أضف أول وحدة من الأعلى لبدء بناء المنهج.</p>
                </div>`;
            }
            toast('تم', data.message || 'تم الحذف', 'success');
            return;
        }

        if (mode === 'delete-lesson' && data?.deleted) {
            const lessonEl = root.querySelector(`[data-curriculum-lesson="${data.deleted.id}"]`);
            const unitEl = lessonEl?.closest('[data-curriculum-unit]');
            lessonEl?.remove();
            if (unitEl) {
                const lessons = unitEl.querySelector('[data-curriculum-lessons]');
                if (lessons && !lessons.querySelector('[data-curriculum-lesson]')) {
                    lessons.innerHTML = '<p class="font-medium text-14px text-gray px-4 sm:px-5 py-4" data-lessons-empty>لا يوجد محتوى بعد — أضف جلسة أو ملفًا أو درسًا نصيًا.</p>';
                }
                syncUnitLessonCount(unitEl);
            }
            toast('تم', data.message || 'تم الحذف', 'success');
        }
    };

    const appendUnit = (unit) => {
        if (!unitsWrap || !unitTemplate) {
            return;
        }
        unitsWrap.querySelector('[data-curriculum-empty]')?.remove();
        let html = unitTemplate.innerHTML
            .replaceAll('__ID__', String(unit.id))
            .replaceAll('__TITLE__', escapeHtml(unit.title))
            .replaceAll('__DELETE_URL__', unit.delete_url || '#')
            .replaceAll('__SESSION_STORE__', unit.session_store_url || '#')
            .replaceAll('__FILE_STORE__', unit.file_store_url || '#')
            .replaceAll('__TEXT_STORE__', unit.text_store_url || '#')
            .replaceAll('__DRAFT__', draftId);
        const holder = document.createElement('div');
        holder.innerHTML = html.trim();
        const node = holder.firstElementChild;
        if (node) {
            const titleEl = node.querySelector('[data-unit-title]');
            if (titleEl) {
                titleEl.textContent = unit.title;
            }
            unitsWrap.appendChild(node);
        }
    };

    const appendLesson = (chapterId, lesson) => {
        const unitEl = root.querySelector(`[data-curriculum-unit="${chapterId}"]`);
        if (!unitEl) {
            return;
        }
        const lessons = unitEl.querySelector('[data-curriculum-lessons]');
        if (!lessons) {
            return;
        }
        lessons.querySelector('[data-lessons-empty]')?.remove();
        lessons.appendChild(buildLessonRow(draftId, lesson));
        syncUnitLessonCount(unitEl);
    };

    root.addEventListener('click', (e) => {
        const previewBtn = e.target.closest('[data-curriculum-preview]');
        if (previewBtn && root.contains(previewBtn)) {
            e.preventDefault();
            openCurriculumFilePreview(previewBtn);
            return;
        }

        const deleteBtn = e.target.closest('[data-curriculum-delete]');
        if (!deleteBtn || !root.contains(deleteBtn) || !deleteForm || !deleteModal) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();

        pendingDelete = {
            url: deleteBtn.getAttribute('data-delete-url') || '',
            mode: deleteBtn.getAttribute('data-delete-mode') || 'delete-lesson',
            draftId: deleteBtn.getAttribute('data-delete-draft-id')
                || wrap.getAttribute('data-draft-id')
                || draftId
                || '',
        };

        deleteForm.action = pendingDelete.url || '#';
        if (deleteTitle) {
            deleteTitle.textContent = deleteBtn.getAttribute('data-delete-title') || 'تأكيد الحذف';
        }
        if (deleteMessage) {
            deleteMessage.textContent = deleteBtn.getAttribute('data-delete-message')
                || 'هل أنت متأكد من حذف هذا العنصر؟ لا يمكن التراجع بعد الحذف.';
        }
        if (deleteItem) {
            const itemText = deleteBtn.getAttribute('data-delete-item') || '';
            if (itemText) {
                deleteItem.textContent = itemText;
                deleteItem.classList.remove('hidden');
            } else {
                deleteItem.textContent = '';
                deleteItem.classList.add('hidden');
            }
        }

        openOverlay(deleteModal);
    });

    deleteModal?.querySelectorAll('[data-overlay="#instructor-confirm-delete-modal"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            pendingDelete = null;
        });
    });

    if (deleteForm && !deleteForm.dataset.curriculumDeleteBound) {
        deleteForm.dataset.curriculumDeleteBound = '1';
        deleteForm.addEventListener('submit', async (e) => {
            if (!pendingDelete?.url || !/\/curriculum\//.test(pendingDelete.url)) {
                return;
            }
            e.preventDefault();

            const submitBtn = deleteForm.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.toggle('opacity-70', true);
            }

            const body = new FormData();
            body.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
            if (pendingDelete.draftId) {
                body.append('draft_id', pendingDelete.draftId);
            }

            try {
                const response = await fetch(pendingDelete.url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body,
                    credentials: 'same-origin',
                });

                let data = null;
                try {
                    data = await response.json();
                } catch (_) {
                    data = null;
                }

                if (!response.ok) {
                    toast('خطأ', firstValidationError(data) || 'تعذر الحذف', 'error');
                    return;
                }

                applyDeleteResult(pendingDelete.mode, data);
                closeOverlay(deleteModal);
                pendingDelete = null;
            } catch (_) {
                toast('خطأ', 'فشل الاتصال بالخادم', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-70');
                }
            }
        });
    }

    root.addEventListener('submit', async (e) => {
        const form = e.target.closest('form[data-curriculum-ajax]');
        if (!form || !root.contains(form)) {
            return;
        }
        e.preventDefault();

        const mode = form.getAttribute('data-curriculum-ajax');
        if (mode === 'delete-chapter' || mode === 'delete-lesson') {
            return;
        }

        const errorEl = form.closest('details')?.querySelector('[data-curriculum-form-error]')
            || form.parentElement?.querySelector('[data-curriculum-form-error]');
        if (errorEl) {
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
        }

        const liveDraft = wrap.getAttribute('data-draft-id') || draftId;
        form.querySelectorAll('[data-draft-id-input]').forEach((input) => {
            if (liveDraft) {
                input.value = liveDraft;
            }
        });

        setBusy(form, true);
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: new FormData(form),
                credentials: 'same-origin',
            });

            let data = null;
            try {
                data = await response.json();
            } catch (_) {
                data = null;
            }

            if (!response.ok) {
                const msg = firstValidationError(data);
                if (errorEl) {
                    errorEl.textContent = msg;
                    errorEl.classList.remove('hidden');
                }
                toast('خطأ في الحقل', msg, 'error');
                return;
            }

            if (mode === 'chapter' && data?.unit) {
                appendUnit(data.unit);
                form.reset();
                toast('تم', data.message || 'تمت إضافة الوحدة', 'success');
                return;
            }

            if ((mode === 'session' || mode === 'file' || mode === 'text') && data?.lesson) {
                appendLesson(data.chapter_id, data.lesson);
                form.reset();
                const details = form.closest('details');
                if (details) {
                    details.open = false;
                }
                toast('تم', data.message || 'تمت الإضافة', 'success');
                return;
            }

            toast('تم', data?.message || 'تم بنجاح', 'success');
        } catch (_) {
            const msg = 'فشل الاتصال بالخادم';
            if (errorEl) {
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
            }
            toast('خطأ', msg, 'error');
        } finally {
            setBusy(form, false);
        }
    });
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
