import type { ApplicationStatus, PaymentStatus } from './statuses';

export type ApiResult<T> = { data: T; meta: { requestId: string; mocked: boolean } };
export type PaymentSummary = { status: PaymentStatus; amount: 1000; currency: 'KES'; reference?: string };
export type ApplicationSummary = { id: string; number: string; status: ApplicationStatus; completion: number; payment: PaymentSummary };

export interface AdmissionsApi {
    getApplication(id: string, signal?: AbortSignal): Promise<ApiResult<ApplicationSummary>>;
    refreshPayment(id: string, signal?: AbortSignal): Promise<ApiResult<PaymentSummary>>;
    saveDraft(id: string, values: Record<string, unknown>, signal?: AbortSignal): Promise<ApiResult<{ savedAt: string }>>;
}

const mockApplication: ApplicationSummary = {
    id: 'mock-application', number: 'MC/APL/SEP2026/000001', status: 'DRAFT', completion: 72,
    payment: { status: 'NOT_STARTED', amount: 1000, currency: 'KES' },
};

const wait = <T>(value: T, signal?: AbortSignal) => new Promise<T>((resolve, reject) => {
    const timer = window.setTimeout(() => resolve(value), 280);
    signal?.addEventListener('abort', () => { window.clearTimeout(timer); reject(new DOMException('Cancelled', 'AbortError')); });
});

export const mockAdmissionsApi: AdmissionsApi = {
    async getApplication(_id, signal) { return wait({ data: mockApplication, meta: { requestId: crypto.randomUUID(), mocked: true } }, signal); },
    async refreshPayment(_id, signal) { return wait({ data: mockApplication.payment, meta: { requestId: crypto.randomUUID(), mocked: true } }, signal); },
    async saveDraft(_id, _values, signal) { return wait({ data: { savedAt: new Date().toISOString() }, meta: { requestId: crypto.randomUUID(), mocked: true } }, signal); },
};

export const admissionsApi: AdmissionsApi = import.meta.env.PROD
    ? {
        async getApplication() { throw new Error('Admissions API is not present in the OpenAPI contract.'); },
        async refreshPayment() { throw new Error('Payment API is not present in the OpenAPI contract.'); },
        async saveDraft() { throw new Error('Draft API is not present in the OpenAPI contract.'); },
    }
    : mockAdmissionsApi;
