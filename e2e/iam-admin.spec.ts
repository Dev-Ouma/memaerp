import { expect, test } from '@playwright/test';
import { AdminLoginPage } from './pages/admin-login-page';

test.describe('MOD-00-01 IAM administration', () => {
  test('rejects invalid credentials without disclosing account details', async ({ page }) => {
    const login = new AdminLoginPage(page);
    await login.goto();
    await login.login('admin@mema.ac.ke', 'incorrect-password');
    await expect(login.errorAlert).toContainText('Sign in failed');
    await expect(page).toHaveURL(/\/login/);
  });

  test('authenticates and renders live protected IAM data', async ({ page }) => {
    const login = new AdminLoginPage(page);
    await login.goto();
    await login.login('EMP-000001', 'password123');
    await expect(page).toHaveURL('http://localhost:3015/');

    await page.goto('/security');
    await expect(page.getByRole('heading', { name: 'Identity & Access Management' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'User account governance' })).toBeVisible();
    await expect(page.getByText('admin@mema.ac.ke')).toBeVisible();
    await expect(page.getByText('system-admin · institution')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Role directory' })).toBeVisible();
  });

  test('password reset request uses a generic response', async ({ page }) => {
    await page.goto('/reset-password');
    await page.getByLabel('Institutional email').fill('unknown@mema.ac.ke');
    await page.getByRole('button', { name: 'Send reset instructions' }).click();
    await expect(page.getByText('If that account exists, password reset instructions have been issued.')).toBeVisible();
  });
});
