export type DraftSaveResult = {
    savedAt: string;
    lockVersion: number;
    completionPercent: number;
};

type ValidationErrorPayload = {
    message?: string;
    errors?: Record<string, string[]>;
};

const csrfToken = (): string => {
    const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;

    if (!token) {
        throw new Error('The security token is missing. Refresh the page and try again.');
    }

    return token;
};

const errorMessage = async (response: Response): Promise<string> => {
    const payload = await response.json().catch(() => null) as ValidationErrorPayload | null;
    const firstValidationError = payload?.errors ? Object.values(payload.errors).flat()[0] : null;

    return firstValidationError ?? payload?.message ?? 'The application could not be saved.';
};

export const admissionsApi = {
    async saveDraft(id: string, values: Record<string, FormDataEntryValue>, signal?: AbortSignal): Promise<DraftSaveResult> {
        const response = await fetch(`/admissions/applications/${encodeURIComponent(id)}`, {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(values),
            signal,
        });

        if (!response.ok) {
            throw new Error(await errorMessage(response));
        }

        return response.json() as Promise<DraftSaveResult>;
    },
};
