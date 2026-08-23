import { type Locator, type Page } from '@playwright/test';

export class AdminLoginPage {
  readonly loginInput: Locator;
  readonly passwordInput: Locator;
  readonly submitButton: Locator;
  readonly errorAlert: Locator;

  constructor(private readonly page: Page) {
    this.loginInput = page.getByLabel('Username or email');
    this.passwordInput = page.getByLabel('Password');
    this.submitButton = page.getByRole('button', { name: 'Sign in' });
    this.errorAlert = page.getByText('Sign in failed');
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
