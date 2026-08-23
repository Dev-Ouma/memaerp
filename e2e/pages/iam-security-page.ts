import { type Page } from '@playwright/test';

export class IamSecurityPage {
  constructor(readonly page: Page) {}

  async goto() { await this.page.goto('/security'); }

  async provisionUser(values: { given: string; family: string; email: string; username: string; identifier: string; password: string }) {
    await this.page.getByRole('button', { name: 'Provision user' }).click();
    await this.page.getByLabel('Given name').fill(values.given);
    await this.page.getByLabel('Family name').fill(values.family);
    await this.page.getByLabel('Institutional email').fill(values.email);
    await this.page.getByLabel('Username').fill(values.username);
    await this.page.getByLabel('Institutional identifier').fill(values.identifier);
    await this.page.getByLabel('Temporary password').fill(values.password);
    await this.page.getByRole('button', { name: 'Create pending account' }).click();
  }

  async manageUser(email: string) {
    const row = this.page.getByRole('row').filter({ hasText: email });
    await row.getByRole('button', { name: 'Manage' }).click();
  }
}
