import { type Locator, type Page } from '@playwright/test';

export class AdminLoginPage {
  readonly loginInput: Locator;
  readonly passwordInput: Locator;
  readonly submitButton: Locator;
  readonly errorAlert: Locator;

  constructor(private readonly page: Page) {
    this.loginInput = page.getByLabel(/Username or email|Institutional Email/i);
    this.passwordInput = page.getByLabel(/Password|ERP Security Key/i);
    this.submitButton = page.getByRole('button', { name: /Sign in|Sign In to ERP/i });
    this.errorAlert = page.getByText(/Sign in failed|Access Denied/i);
  }

  async goto(): Promise<void> {
    await this.page.goto('/login');
  }

  async login(identifier: string, password: string): Promise<void> {
    await this.loginInput.fill(identifier);
    await this.passwordInput.fill(password);
    await this.submitButton.click();
  }
}
