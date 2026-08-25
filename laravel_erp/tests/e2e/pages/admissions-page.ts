import { type Locator, type Page } from '@playwright/test';

export class AdmissionsPage {
    readonly heading: Locator;
    readonly search: Locator;

    constructor(private readonly page: Page) {
        this.heading = page.getByRole('heading', { level: 1 });
        this.search = page.getByRole('textbox', { name: 'Search programmes' });
    }

    async openCatalogue() { await this.page.goto('/programmes'); }
    async searchFor(term: string) { await this.search.fill(term); }
}
