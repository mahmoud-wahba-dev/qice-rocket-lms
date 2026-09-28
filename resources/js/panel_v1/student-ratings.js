/**
 * Course / instructor star-rating modals (course player).
 */
export function initStudentRatings(root = document) {
    const courseLabels = {
        1: 'ضعيف — يحتاج تحسينًا كبيرًا',
        2: 'مقبول — هناك مجال للتحسين',
        3: 'جيد — محتوى مفيد',
        4: 'ممتاز جداً!',
        5: 'دورة ممتازة ومحتوى قيم جداً! 🔥',
    };
    const instructorLabels = {
        1: 'ضعيف',
        2: 'مقبول',
        3: 'جيد',
        4: 'ممتاز جداً! ✨',
        5: 'تجربة رائعة مع المدرب! 🌟',
    };

    const toast = (title, msg, type = 'success') => {
        if (typeof window.showCartToast === 'function') {
            window.showCartToast(title, msg, type);
        }
    };

    const openOverlay = (el) => {
        if (!el) return;
        if (window.HSOverlay?.open) {
            window.HSOverlay.open(el);
        } else {
            el.classList.remove('hidden');
        }
    };

    const closeOverlay = (el) => {
        if (!el) return;
        if (window.HSOverlay?.close) {
            window.HSOverlay.close(el);
        } else {
            el.classList.add('hidden');
        }
    };

    const paintStars = (wrap, value) => {
        wrap.querySelectorAll('[data-star]').forEach((btn) => {
            const n = parseInt(btn.getAttribute('data-star') || '0', 10);
            const icon = btn.querySelector('[data-star-icon]');
            if (!icon) return;
            if (n <= value) {
                icon.classList.remove('opacity-25', 'text-gray');
                icon.classList.add('text-[#F5C451]');
            } else {
                icon.classList.add('opacity-25', 'text-gray');
            }
        });
    };

    root.querySelectorAll('[data-rating-modal]').forEach((modal) => {
        const kind = modal.getAttribute('data-rating-modal');
        const form = modal.querySelector('[data-rating-form]');
        const stars = modal.querySelector('[data-rating-stars]');
        const valueInput = modal.querySelector('[data-rating-value]');
        const labelEl = modal.querySelector('[data-rating-label]');
        const errorEl = modal.querySelector('[data-rating-error]');
        const labels = kind === 'instructor' ? instructorLabels : courseLabels;
        if (!form || !stars || !valueInput) return;

        let current = parseInt(valueInput.value || '5', 10) || 5;
        paintStars(stars, current);
        if (labelEl) labelEl.textContent = labels[current] || '';

        stars.querySelectorAll('[data-star]').forEach((btn) => {
            btn.addEventListener('click', () => {
                current = parseInt(btn.getAttribute('data-star') || '5', 10) || 5;
                valueInput.value = String(current);
                paintStars(stars, current);
                if (labelEl) labelEl.textContent = labels[current] || '';
            });
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (errorEl) {
                errorEl.classList.add('hidden');
                errorEl.textContent = '';
            }
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-70');
            }
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
                    const msg = data?.message
                        || data?.toast_alert?.msg
                        || (data?.errors ? Object.values(data.errors).flat()[0] : null)
                        || 'تعذر إرسال التقييم';
                    if (errorEl) {
                        errorEl.textContent = msg;
                        errorEl.classList.remove('hidden');
                    }
                    toast('خطأ', msg, 'error');
                    return;
                }
                closeOverlay(modal);
                toast('تم', data?.message || 'تم إرسال تقييمك بنجاح', 'success');
            } catch (_) {
                toast('خطأ', 'فشل الاتصال بالخادم', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-70');
                }
            }
        });
    });

    root.querySelectorAll('[data-open-rate-course]').forEach((btn) => {
        btn.addEventListener('click', () => openOverlay(document.getElementById('student-rate-course-modal')));
    });
    root.querySelectorAll('[data-open-rate-instructor]').forEach((btn) => {
        btn.addEventListener('click', () => openOverlay(document.getElementById('student-rate-instructor-modal')));
    });
}
