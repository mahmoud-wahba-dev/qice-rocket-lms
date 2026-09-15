function formatBytes(bytes) {
    const n = Number(bytes) || 0;
    if (n < 1024) return `${n} B`;
    if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;
    return `${(n / (1024 * 1024)).toFixed(1)} MB`;
}

function fileKind(file) {
    const type = (file?.type || '').toLowerCase();
    const name = (file?.name || '').toLowerCase();
    if (type.startsWith('image/') || /\.(png|jpe?g|gif|webp|svg|bmp)$/i.test(name)) return 'image';
    if (type === 'application/pdf' || name.endsWith('.pdf')) return 'pdf';
    if (type.startsWith('video/') || /\.(mp4|webm|mov|m4v)$/i.test(name)) return 'video';
    if (
        type.includes('word') ||
        type.includes('document') ||
        /\.(docx?|rtf|txt)$/i.test(name)
    ) {
        return 'doc';
    }
    return 'file';
}

function kindIcon(kind) {
    if (kind === 'pdf') return 'icon-[tabler--file-type-pdf] size-8 text-[#EF4444]';
    if (kind === 'video') return 'icon-[tabler--video] size-8 text-primary';
    if (kind === 'doc') return 'icon-[tabler--file-text] size-8 text-[#2563EB]';
    return 'icon-[tabler--file] size-8 text-gray';
}

function kindLabel(kind) {
    return (
        {
            image: 'صورة',
            pdf: 'PDF',
            video: 'فيديو',
            doc: 'مستند',
            file: 'ملف',
        }[kind] || 'ملف'
    );
}

function clearObjectUrls(root) {
    root.querySelectorAll('[data-v1-thumb]').forEach((el) => {
        const src = el.getAttribute('src') || '';
        if (src.startsWith('blob:')) {
            URL.revokeObjectURL(src);
        }
    });
}

function buildPreviewCard(file, objectUrl, mediaMode) {
    const kind = fileKind(file);
    const size = formatBytes(file.size);
    const wrap = document.createElement('div');
    wrap.className = 'rounded-14px border border-d9 bg-white overflow-hidden text-start';
    wrap.setAttribute('data-v1-preview-card', '');
    wrap.setAttribute('data-v1-local-preview', '');

    if (mediaMode && (kind === 'image' || kind === 'video') && objectUrl) {
        const mediaHtml =
            kind === 'image'
                ? `<img src="${objectUrl}" alt="" class="size-full object-cover" data-v1-thumb>`
                : `<video src="${objectUrl}" class="size-full object-cover" controls muted playsinline data-v1-thumb></video>`;

        wrap.innerHTML = `
          <div class="w-full bg-fa center overflow-hidden aspect-[16/10] max-h-56">${mediaHtml}</div>
          <div class="px-4 py-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="font-bold text-14px text-primary truncate" title="${file.name}">${file.name}</p>
              <p class="font-medium text-12px text-gray mt-0.5">${size} • ${kindLabel(kind)}</p>
            </div>
            <button type="button" class="font-bold text-13px text-[#EF4444] shrink-0" data-v1-remove-preview>إزالة</button>
          </div>
        `;
        return wrap;
    }

    const thumb =
        kind === 'image' && objectUrl
            ? `<img src="${objectUrl}" alt="" class="size-full object-cover min-h-20" data-v1-thumb>`
            : `<span class="${kindIcon(kind)}"></span>`;

    wrap.innerHTML = `
      <div class="flex items-stretch gap-3">
        <div class="w-20 sm:w-24 shrink-0 bg-fa center overflow-hidden border-e border-d9">${thumb}</div>
        <div class="flex-1 min-w-0 py-3 pe-2">
          <p class="font-bold text-14px sm:text-15px text-primary truncate" title="${file.name}">${file.name}</p>
          <p class="font-medium text-12px text-gray mt-0.5">${size} <span class="mx-1 text-d9">•</span> ${kindLabel(kind)}</p>
          <div class="flex flex-wrap items-center gap-3 mt-2">
            <button type="button" class="font-bold text-13px text-[#EF4444]" data-v1-remove-preview>إزالة</button>
          </div>
        </div>
      </div>
    `;
    return wrap;
}

function bindUpload(root) {
    if (root.dataset.v1Bound === '1') return;
    root.dataset.v1Bound = '1';

    const input = root.querySelector('[data-v1-file-input]');
    const preview = root.querySelector('[data-v1-preview]');
    const existing = root.querySelector('[data-v1-existing]');
    const selectedName = root.querySelector('[data-v1-selected-name]');
    const errorEl = root.querySelector('[data-v1-error]');
    const dropzone = root.querySelector('[data-v1-dropzone]');
    const mediaMode = root.dataset.v1Media === '1';
    if (!input || !preview) return;

    const render = () => {
        clearObjectUrls(preview);
        preview.innerHTML = '';
        const files = Array.from(input.files || []);
        if (!files.length) {
            preview.classList.add('hidden');
            if (existing) existing.classList.remove('opacity-40');
            if (selectedName) {
                selectedName.classList.add('hidden');
                selectedName.textContent = '';
            }
            return;
        }

        preview.classList.remove('hidden');
        if (existing) existing.classList.add('opacity-40');
        if (selectedName) {
            selectedName.classList.remove('hidden');
            selectedName.textContent =
                files.length === 1 ? files[0].name : `${files.length} ملفات محددة`;
        }

        files.forEach((file) => {
            const kind = fileKind(file);
            const objectUrl =
                kind === 'image' || kind === 'video' ? URL.createObjectURL(file) : null;
            preview.appendChild(buildPreviewCard(file, objectUrl, mediaMode));
        });
    };

    const clearInput = () => {
        input.value = '';
        render();
        if (errorEl) {
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
        }
    };

    input.addEventListener('change', render);

    preview.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-v1-remove-preview]');
        if (!btn) return;
        e.preventDefault();
        clearInput();
    });

    if (dropzone) {
        ['dragenter', 'dragover'].forEach((evt) => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.add('border-primary', 'bg-[#D1FAE5]/70');
            });
        });
        ['dragleave', 'drop'].forEach((evt) => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-primary', 'bg-[#D1FAE5]/70');
            });
        });
        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer?.files;
            if (!files?.length) return;
            const multiple = root.dataset.v1Multiple === '1';
            const dt = new DataTransfer();
            const list = multiple ? Array.from(files) : [files[0]];
            list.forEach((f) => dt.items.add(f));
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }
}

export function initPanelV1FileUploads(scope = document) {
    scope.querySelectorAll('[data-v1-file-upload]').forEach(bindUpload);
}
