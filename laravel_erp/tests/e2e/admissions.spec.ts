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
