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
    let controller: AbortController | null = null;
    const save = debounce(async () => {
        if (!status) return;
        controller?.abort();
        controller = new AbortController();
        status.textContent = 'Saving…';
        try {
            const values = Object.fromEntries(new FormData(form).entries());
            const result = await admissionsApi.saveDraft(form.dataset.applicationId ?? 'draft', values, controller.signal);
            const lockVersion = form.querySelector<HTMLInputElement>('input[name="lock_version"]');
            if (lockVersion) lockVersion.value = String(result.lockVersion);
            status.textContent = `Saved ${new Date(result.savedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') return;
            status.textContent = error instanceof Error ? error.message : 'Could not autosave — your entries remain on this page.';
        }
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

document.querySelectorAll<HTMLButtonElement>('[data-export-table]').forEach((button) => button.addEventListener('click', () => {
    const table = document.getElementById(button.dataset.exportTable ?? '');
    if (!(table instanceof HTMLTableElement)) return;
    const format = button.dataset.format;
    if (format === 'pdf') { window.print(); return; }
    const rows = [...table.rows].map((row) => [...row.cells].map((cell) => `"${cell.innerText.replaceAll('"', '""')}"`).join(','));
    const content = format === 'xls' ? `<html><body>${table.outerHTML}</body></html>` : rows.join('\n');
    const mime = format === 'xls' ? 'application/vnd.ms-excel' : 'text/csv;charset=utf-8';
    const blob = new Blob([content], { type: mime });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `mema-admissions-${new Date().toISOString().slice(0, 10)}.${format}`;
    link.click();
    URL.revokeObjectURL(link.href);
}));
