import { test, expect } from '@playwright/test';
import { AdminLoginPage } from './pages/admin-login-page';

test.describe('Admin student SIS (MOD-01-06)', () => {
  test.beforeEach(async ({ page }) => {
    const login = new AdminLoginPage(page);
    await login.goto();
    await login.login('registrar@mema.ac.ke', 'password');
  });

  test('loads student dashboard and registry from live API', async ({ page }) => {
    await page.goto('/students');
    await expect(page.getByRole('heading', { name: /Student Information System/i })).toBeVisible();
    await expect(page.getByText('Active students')).toBeVisible();
    await expect(page.getByText('Matriculation queue')).toBeVisible();
    await expect(page.getByText(/GET \/api\/v1\/students/i)).toBeVisible();
  });

  test('shows matriculation queue panel for registrar', async ({ page }) => {
    await page.goto('/students');
    await expect(page.getByRole('heading', { name: /Matriculation Queue/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /Matriculate/i })).toBeVisible();
  });
});
