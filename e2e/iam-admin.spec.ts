import { expect, test } from '@playwright/test';
import { AdminLoginPage } from './pages/admin-login-page';
import { IamSecurityPage } from './pages/iam-security-page';
import { createHmac } from 'node:crypto';

function totp(secret: string): string {
  const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  let bits = '';
  for (const character of secret.replace(/=+$/, '').toUpperCase()) bits += alphabet.indexOf(character).toString(2).padStart(5, '0');
  const key = Buffer.from((bits.match(/.{8}/g) ?? []).map((byte) => Number.parseInt(byte, 2)));
  const counter = Buffer.alloc(8); counter.writeBigUInt64BE(BigInt(Math.floor(Date.now() / 30_000)));
  const digest = createHmac('sha1', key).update(counter).digest(); const offset = digest[digest.length - 1]! & 0x0f;
  return ((digest.readUInt32BE(offset) & 0x7fffffff) % 1_000_000).toString().padStart(6, '0');
}

async function loginAsAdmin(page: import('@playwright/test').Page) {
  const login = new AdminLoginPage(page); await login.goto(); await login.login('EMP-000001', 'password123');
  await expect(page).toHaveURL('http://localhost:3015/');
}

async function loginUser(page: import('@playwright/test').Page, identity: string) {
  const login = new AdminLoginPage(page); await login.goto(); await login.login(identity, 'password123');
  await expect(page).toHaveURL('http://localhost:3015/');
}

test.describe('MOD-00-01 IAM administration', () => {
  test.beforeEach(async ({ page }) => {
    page.on('pageerror', (error) => console.error(`Browser page error: ${error.message}`));
  });

  test('rejects invalid credentials without disclosing account details', async ({ page }) => {
    const login = new AdminLoginPage(page);
    await login.goto();
    await login.login('admin@mema.ac.ke', 'incorrect-password');
    await expect(login.errorAlert).toContainText('Sign in failed');
    await expect(page).toHaveURL(/\/login/);
  });

  test('authenticates and renders live protected IAM data', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto('/security');
    await expect(page.getByRole('heading', { name: 'Identity & Access Management' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'User account governance' })).toBeVisible();
    await expect(page.getByText('admin@mema.ac.ke')).toBeVisible();
    await expect(page.getByText('system-admin · institution')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Role directory' })).toBeVisible();
  });

  test('provisions, activates, assigns a scoped role, and performs MFA recovery', async ({ page }) => {
    await loginAsAdmin(page); const iam = new IamSecurityPage(page); await iam.goto();
    const suffix = `${Date.now()}-${test.info().workerIndex}`; const email = `web.iam.${suffix}@mema.ac.ke`;
    await iam.provisionUser({ given: 'Web', family: 'Operator', email, username: `web-${suffix}`, identifier: `WEB-${suffix}`, password: 'Temporary-Password-2026!' });
    await expect(page.getByText('User provisioned in PENDING state.')).toBeVisible();
    await iam.manageUser(email);
    await page.getByLabel('New status').selectOption('ACTIVE');
    await page.getByLabel('Reason', { exact: true }).fill('Identity documents verified.');
    await page.getByRole('button', { name: 'Update status' }).click();
    await expect(page.getByText('Account status updated and affected sessions revoked.')).toBeVisible();
    await page.getByLabel('Role').selectOption({ label: 'Content Editor (content-editor)' });
    await page.getByLabel('Scope', { exact: true }).selectOption('institution');
    await page.getByLabel('Grant reason').fill('Approved content operations duty.');
    await page.getByRole('button', { name: 'Assign role' }).click();
    await expect(page.getByText('Scoped role assigned; the user must sign in again.')).toBeVisible();
    await page.getByLabel('Verification and recovery reason').fill('Helpdesk identity verification completed.');
    await page.getByRole('button', { name: 'Reset MFA and sessions' }).click();
    await expect(page.getByText('MFA reset completed and all user sessions revoked.')).toBeVisible();
  });

  test('enrolls and disables authenticator MFA from account security', async ({ page }) => {
    await loginUser(page, 'lecturer@mema.ac.ke'); await page.goto('/account-security');
    await page.getByRole('button', { name: 'Set up authenticator' }).click();
    await expect(page.getByAltText('Authenticator enrollment QR code')).toBeVisible();
    const manual = await page.getByText(/^Manual key:/).textContent(); const secret = manual!.replace('Manual key:', '').trim();
    await page.getByLabel('Six-digit code').fill(totp(secret)); await page.getByRole('button', { name: 'Confirm MFA' }).click();
    await expect(page.getByText('Recovery codes — save once')).toBeVisible();
    await page.getByLabel('Current password to disable MFA').fill('password123');
    await page.getByRole('button', { name: 'Disable MFA' }).click();
    await expect(page.getByText('MFA disabled.')).toBeVisible();
  });

  test('lists the active device and signs out everywhere', async ({ page }) => {
    await loginUser(page, 'finance@mema.ac.ke'); await page.goto('/account-security');
    await expect(page.getByText('Web browser')).toBeVisible();
    await page.getByRole('button', { name: 'Sign out everywhere' }).click();
    await expect(page).toHaveURL(/\/login/);
  });

  test('changes the password and invalidates the current session', async ({ page }) => {
    await loginUser(page, 'registrar@mema.ac.ke'); await page.goto('/account-security');
    await page.getByLabel('Current password', { exact: true }).fill('password123');
    await page.getByLabel('New password', { exact: true }).fill('Registrar-New-Password-2026!');
    await page.getByLabel('Confirm new password', { exact: true }).fill('Registrar-New-Password-2026!');
    await page.getByRole('button', { name: 'Change password and sign out' }).click();
    await expect(page).toHaveURL(/\/login/);
  });

  test('revokes an individual tracked browser session', async ({ page }) => {
    await loginUser(page, 'auditor@mema.ac.ke'); await page.goto('/account-security');
    await page.getByRole('button', { name: 'Revoke session' }).click();
    await page.goto('/account-security');
    await expect(page).toHaveURL(/\/login/);
  });

  test('password reset request uses a generic response', async ({ page }) => {
    await page.goto('/reset-password');
    await page.getByLabel('Institutional email').fill('unknown@mema.ac.ke');
    await page.getByRole('button', { name: 'Send reset instructions' }).click();
    await expect(page.getByText('If that account exists, password reset instructions have been issued.')).toBeVisible();
  });
});
