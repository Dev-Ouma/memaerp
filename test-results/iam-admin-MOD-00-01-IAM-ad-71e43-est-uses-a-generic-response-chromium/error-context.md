# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: iam-admin.spec.ts >> MOD-00-01 IAM administration >> password reset request uses a generic response
- Location: e2e/iam-admin.spec.ts:27:7

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: getByText('If that account exists, password reset instructions have been issued.')
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for getByText('If that account exists, password reset instructions have been issued.')

```

```yaml
- banner:
  - img
  - paragraph: MEMA ERP
  - paragraph: ERP Administration
- main:
  - heading "Reset password" [level=1]
  - paragraph: Enter your institutional email. The response never reveals whether an account exists.
  - alert:
    - img
    - heading "Unable to reset password" [level=5]
    - text: Request failed with status code 404
  - text: Institutional email
  - textbox "Institutional email": unknown@mema.ac.ke
  - button "Send reset instructions"
  - link "Back to sign in":
    - /url: /login
- status:
  - img
  - text: Static route
  - button "Hide static indicator":
    - img
- alert
```

# Test source

```ts
  1  | import { expect, test } from '@playwright/test';
  2  | import { AdminLoginPage } from './pages/admin-login-page';
  3  | 
  4  | test.describe('MOD-00-01 IAM administration', () => {
  5  |   test('rejects invalid credentials without disclosing account details', async ({ page }) => {
  6  |     const login = new AdminLoginPage(page);
  7  |     await login.goto();
  8  |     await login.login('admin@mema.ac.ke', 'incorrect-password');
  9  |     await expect(login.errorAlert).toContainText('Sign in failed');
  10 |     await expect(page).toHaveURL(/\/login/);
  11 |   });
  12 | 
  13 |   test('authenticates and renders live protected IAM data', async ({ page }) => {
  14 |     const login = new AdminLoginPage(page);
  15 |     await login.goto();
  16 |     await login.login('EMP-000001', 'password123');
  17 |     await expect(page).toHaveURL('http://localhost:3015/');
  18 | 
  19 |     await page.goto('/security');
  20 |     await expect(page.getByRole('heading', { name: 'Identity & Access Management' })).toBeVisible();
  21 |     await expect(page.getByRole('heading', { name: 'User account governance' })).toBeVisible();
  22 |     await expect(page.getByText('admin@mema.ac.ke')).toBeVisible();
  23 |     await expect(page.getByText('system-admin · institution')).toBeVisible();
  24 |     await expect(page.getByRole('heading', { name: 'Role directory' })).toBeVisible();
  25 |   });
  26 | 
  27 |   test('password reset request uses a generic response', async ({ page }) => {
  28 |     await page.goto('/reset-password');
  29 |     await page.getByLabel('Institutional email').fill('unknown@mema.ac.ke');
  30 |     await page.getByRole('button', { name: 'Send reset instructions' }).click();
> 31 |     await expect(page.getByText('If that account exists, password reset instructions have been issued.')).toBeVisible();
     |                                                                                                           ^ Error: expect(locator).toBeVisible() failed
  32 |   });
  33 | });
  34 | 
```