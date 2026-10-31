function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function openModal(el) {
    if (!el) {
        return;
    }
    if (window.HSOverlay?.open) {
        window.HSOverlay.open(el);
        return;
    }
    el.classList.remove('hidden');
    el.classList.add('open');
}

function closeModal(el) {
    if (!el) {
        return;
    }
    if (window.HSOverlay?.close) {
        window.HSOverlay.close(el);
        return;
    }
    el.classList.add('hidden');
    el.classList.remove('open');
}

export function initCertificateNamesModal() {
    const modal = document.querySelector('[data-certificate-names-modal]');
    if (!modal) {
        return;
    }

    const form = modal.querySelector('[data-certificate-names-form]');
    const idInput = modal.querySelector('[data-certificate-names-id]');
    const viewInput = modal.querySelector('[data-certificate-names-view]');
    const errorEl = modal.querySelector('[data-certificate-names-error]');
    const submitBtn = modal.querySelector('[data-certificate-names-submit]');
    const urlTemplate = modal.getAttribute('data-complete-url-template') || '';

    const openForCertificate = (certificateId, { view = false } = {}) => {
        if (!certificateId) {
            return;
        }
        if (idInput) {
            idInput.value = String(certificateId);
        }
        if (viewInput) {
            viewInput.value = view ? '1' : '0';
        }
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }
        openModal(modal);
    };

    document.querySelectorAll('[data-open-certificate-names]').forEach((btn) => {
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            const certId = btn.getAttribute('data-certificate-id') || idInput?.value;
            const view = btn.getAttribute('data-certificate-view') === '1';
            openForCertificate(certId, { view });
        });
    });

    if (modal.getAttribute('data-auto-open') === '1') {
        const initialId = modal.getAttribute('data-initial-certificate-id') || idInput?.value;
        if (initialId) {
            openForCertificate(initialId);
        }
    }

    if (!form) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const certId = idInput?.value;
        if (!certId || !urlTemplate) {
            return;
        }

        const action = urlTemplate.replace('__ID__', String(certId));
        const fd = new FormData(form);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const res = await fetch(action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: fd,
                credentials: 'same-origin',
            });
            const payload = await res.json().catch(() => ({}));
            if (!res.ok || !payload.ok) {
                const msg =
                    payload.message ||
                    payload.errors?.trainee_name_ar?.[0] ||
                    payload.errors?.trainee_name_en?.[0] ||
                    'تعذر حفظ الاسم. حاول مرة أخرى.';
                if (errorEl) {
                    errorEl.textContent = msg;
                    errorEl.classList.remove('hidden');
                }
                return;
            }

            closeModal(modal);
            if (payload.download_url) {
                window.open(payload.download_url, '_blank', 'noopener');
                window.setTimeout(() => window.location.reload(), 400);
            }
        } catch (_) {
            if (errorEl) {
                errorEl.textContent = 'تعذر الاتصال بالخادم. حاول مرة أخرى.';
                errorEl.classList.remove('hidden');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    });
}
