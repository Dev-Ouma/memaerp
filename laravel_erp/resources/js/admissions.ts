import { admissionsApi } from './admissions/api';

const debounce = (callback: () => void, delay = 700) => {
    let timer = 0;
    return () => { window.clearTimeout(timer); timer = window.setTimeout(callback, delay); };
};

document.querySelectorAll<HTMLElement>('[data-filter-grid]').forEach((grid) => {
    const search = document.querySelector<HTMLInputElement>('[data-programme-search]');
    const filter = () => {
        const term = search?.value.toLowerCase().trim() ?? '';
        grid.querySelectorAll<HTMLElement>('[data-programme-card]').forEach((card) => {
            card.hidden = !card.textContent?.toLowerCase().includes(term);
        });
    };
    search?.addEventListener('input', filter);
});

document.querySelectorAll<HTMLFormElement>('[data-autosave-form]').forEach((form) => {
    const status = form.querySelector<HTMLElement>('[data-save-status]');
    const save = debounce(async () => {
        if (!status) return;
        status.textContent = 'Saving…';
        try {
            const values = Object.fromEntries(new FormData(form).entries());
            const result = await admissionsApi.saveDraft(form.dataset.applicationId ?? 'draft', values);
            status.textContent = `Saved ${new Date(result.data.savedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
        } catch { status.textContent = 'Could not autosave — your entries remain on this page.'; }
    });
    form.addEventListener('input', save);
});

document.querySelectorAll<HTMLElement>('[data-file-drop]').forEach((drop) => {
    const input = drop.querySelector<HTMLInputElement>('input[type=file]');
    const output = drop.querySelector<HTMLElement>('[data-file-name]');
    ['dragenter', 'dragover'].forEach((name) => drop.addEventListener(name, (event) => { event.preventDefault(); drop.dataset.dragging = 'true'; }));
    ['dragleave', 'drop'].forEach((name) => drop.addEventListener(name, (event) => { event.preventDefault(); delete drop.dataset.dragging; }));
    drop.addEventListener('drop', (event: DragEvent) => { if (input && event.dataTransfer?.files.length) { input.files = event.dataTransfer.files; input.dispatchEvent(new Event('change')); } });
    input?.addEventListener('change', () => { if (output) output.textContent = input.files?.[0]?.name ?? 'No file selected'; });
});

document.querySelectorAll<HTMLButtonElement>('[data-print]').forEach((button) => button.addEventListener('click', () => window.print()));
