import { expect, test } from '@playwright/test';

test.describe('All portals — load and login smoke', () => {
  test('public website loads at :3000', async ({ page }) => {
    const response = await page.goto('http://localhost:3000', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await expect(page).toHaveTitle(/Mema University/i);
    await expect(page.locator('h1, h2, h3').first()).toBeVisible();
  });

  test('applicant portal loads at :3001', async ({ page }) => {
    const response = await page.goto('http://localhost:3001/', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await expect(page.locator('h1, h2, h3').first()).toBeVisible();
  });

  test('student portal login page loads at :3002/login', async ({ page }) => {
    const response = await page.goto('http://localhost:3002/login', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await expect(page.getByRole('heading', { name: /Student Portal Sign In/i })).toBeVisible();
  });

  test('student can sign in with demo credentials', async ({ page }) => {
    await page.goto('http://localhost:3002/login');
    await page.getByRole('button', { name: 'Quick Auto-fill' }).click();
    await page.getByRole('button', { name: /Access My Portal/i }).click();
    await expect(page).toHaveURL(/^http:\/\/localhost:3002\/?$/, { timeout: 20_000 });
    await expect(page.getByText(/Faith|Dashboard|Welcome/i).first()).toBeVisible({ timeout: 15_000 });
  });

  test('admin portal login page loads at :3005/login', async ({ page }) => {
    const response = await page.goto('http://localhost:3005/login', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await expect(page.getByRole('heading', { name: /ERP Administrator Login/i })).toBeVisible();
  });

  test('admin can sign in with demo credentials', async ({ page }) => {
    await page.goto('http://localhost:3005/login');
    await page.getByRole('button', { name: 'System Admin' }).click();
    await page.getByRole('button', { name: /Sign In to ERP/i }).click();
    await expect(page).toHaveURL(/^http:\/\/localhost:3005\/?$/, { timeout: 20_000 });
  });

  test('admin rejects invalid credentials', async ({ page }) => {
    await page.goto('http://localhost:3005/login');
    await page.getByLabel(/Institutional Email/i).fill('admin@mema.ac.ke');
    await page.getByLabel(/ERP Security Key/i).fill('wrong-password');
    await page.getByRole('button', { name: /Sign In to ERP/i }).click();
    await expect(page.getByText(/Access Denied|Sign in failed/i)).toBeVisible();
    await expect(page).toHaveURL(/\/login/);
  });

  test('lecturer portal login loads at :3003/login', async ({ page }) => {
    const response = await page.goto('http://localhost:3003/login', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await expect(page.getByRole('heading', { name: /Lecturer sign in/i })).toBeVisible();
  });

  test('staff portal login loads at :3004/login', async ({ page }) => {
    const response = await page.goto('http://localhost:3004/login', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(200);
    await expect(page.getByRole('heading', { name: /Staff sign in/i })).toBeVisible();
  });

  test('registrar can open staff clearance queue', async ({ page }) => {
    await page.goto('http://localhost:3004/login');
    await page.getByLabel(/Username or email/i).fill('registrar@mema.ac.ke');
    await page.getByLabel(/^Password$/i).fill('password123');
    await page.getByRole('button', { name: /Sign in/i }).click();
    await page.goto('http://localhost:3004/requests', { waitUntil: 'domcontentloaded' });
    await expect(page.getByRole('heading', { name: /Clearance Queue/i })).toBeVisible({ timeout: 20_000 });
  });

  test('admin can open LMS and attendance pages after login', async ({ page }) => {
    await page.goto('http://localhost:3005/login');
    await page.getByRole('button', { name: 'System Admin' }).click();
    await page.getByRole('button', { name: /Sign In to ERP/i }).click();
    await expect(page).toHaveURL(/^http:\/\/localhost:3005\/?$/, { timeout: 20_000 });

    await page.goto('http://localhost:3005/lms');
    await expect(page.getByRole('heading', { name: /LMS Sync Dashboard/i })).toBeVisible({ timeout: 15_000 });

    await page.goto('http://localhost:3005/attendance');
    await expect(page.getByRole('heading', { name: /Attendance Reports/i })).toBeVisible({ timeout: 15_000 });
  });
});
