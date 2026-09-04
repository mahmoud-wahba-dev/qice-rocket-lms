const DEFAULT_FILE_LABEL = '📎 اضغط هنا لرفع الملف بصيغة (PDF أو DOCX)';

function countWords(value) {
    return value
        .trim()
        .split(/\s+/)
        .filter(Boolean).length;
}

export function initAssignmentSubmitModal() {
    const textarea = document.querySelector('[data-assignment-word-limit]');

    if (!textarea) {
        return;
    }

    const currentEl = document.querySelector('[data-assignment-word-current]');
    const maxEl = document.querySelector('[data-assignment-word-max]');
    const fileInput = document.querySelector('[data-assignment-file-input]');
    const fileLabel = document.querySelector('[data-assignment-file-label]');
    const maxWords = Number(textarea.dataset.assignmentWordLimit || 500);

    if (maxEl) {
        maxEl.textContent = String(maxWords);
    }

    const updateWordCount = () => {
        const words = countWords(textarea.value);

        if (currentEl) {
            currentEl.textContent = String(words);
        }
    };

    textarea.addEventListener('input', updateWordCount);
    updateWordCount();

    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', () => {
            const file = fileInput.files?.[0];

            fileLabel.textContent = file
                ? `📎 ${file.name}`
                : DEFAULT_FILE_LABEL;
        });
    }
}
