import { expect, test } from '@playwright/test';
import { AdmissionsPage } from './pages/admissions-page';

test('programme discovery exposes the application journey', async ({ page }) => {
    const admissions = new AdmissionsPage(page);
    await admissions.openCatalogue();
    await expect(admissions.heading).toContainText('Build the skills');
    await expect(page.getByRole('navigation', { name: 'Public navigation' })).toHaveCSS('display', 'flex');
    await expect(page.locator('body')).toHaveCSS('background-color', 'rgb(245, 248, 249)');
    await admissions.searchFor('Computer');
    await expect(page.getByRole('link', { name: 'View & apply' })).toBeVisible();
});

test('registration has required legal, identity and secure-payment guidance', async ({ page }) => {
    await page.goto('/programmes');
    const programme = page.getByRole('article').filter({ has: page.getByRole('heading', { name: 'Computer Science & Engineering', exact: true }) });
    await programme.getByRole('link', { name: 'View & apply' }).click();
    await expect(page.getByRole('heading', { name: 'Start your application' })).toBeVisible();
    await expect(page.getByLabel('Birth certificate or passport number *')).toBeVisible();
    await expect(page.getByText('We never ask for an M-Pesa PIN')).toBeVisible();
    await expect(page.getByRole('checkbox', { name: /creating an account does not mean/ })).toBeVisible();
});

test('public journey is keyboard reachable', async ({ page }, testInfo) => {
    await page.goto('/programmes');
    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: /MEMA College Admissions/ })).toBeFocused();
    await page.keyboard.press('Tab');
    const nextLink = testInfo.project.name === 'mobile-chrome' ? 'Apply now' : 'Programmes';
    await expect(page.getByRole('link', { name: nextLink })).toBeFocused();
});

test('staff can inspect analytics and branded report exports', async ({ page }) => {
    await page.goto('/');
    await page.getByRole('button', { name: 'Sign in to MEMA' }).click();
    await page.waitForURL(/dashboard/);

    await page.goto('/admissions/analytics');
    await expect(page.getByRole('heading', { name: 'Admissions performance' })).toBeVisible();
    await expect(page.getByRole('img', { name: 'Application conversion funnel' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Programme demand' })).toBeVisible();

    await page.goto('/admissions/reports');
    await expect(page.getByRole('heading', { name: 'Admissions report centre' })).toBeVisible();
    await expect(page.getByText('Web preview', { exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'CSV' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Excel (.xls)' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'PDF / Print' })).toBeVisible();
});

test('administrators can manage authoritative admission setups', async ({ page }, testInfo) => {
    await page.goto('/');
    await page.getByRole('button', { name: 'Sign in to MEMA' }).click();
    await page.waitForURL(/dashboard/);
    if (testInfo.project.name === 'mobile-chrome') {
        await page.goto('/admissions/setups');
    } else {
        await page.getByRole('link', { name: 'Admin Setups', exact: true }).click();
    }
    await page.getByPlaceholder('Search setups').fill('fee');
    await page.getByRole('button', { name: 'Filter' }).click();
    await expect(page.getByRole('heading', { name: 'Admin Setups', exact: true })).toBeVisible();
    await expect(page.getByText('Application-fee rules', { exact: true })).toBeVisible();
    await page.getByRole('link', { name: 'Configure' }).click();
    await expect(page.getByRole('heading', { name: 'Application-fee rules' })).toBeVisible();
    await expect(page.getByText('Version 1')).toBeVisible();
});
