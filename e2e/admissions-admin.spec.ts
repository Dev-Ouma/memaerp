import { expect, test as base, type BrowserContext } from '@playwright/test';
import { AdminLoginPage } from './pages/admin-login-page';

type WorkerFixtures = {
  registrarState: Awaited<ReturnType<BrowserContext['storageState']>>;
};

async function authenticatedState(browser: import('@playwright/test').Browser, email: string) {
  const context = await browser.newContext();
  const page = await context.newPage();
  const login = new AdminLoginPage(page);
  await login.goto();
  await login.login(email, 'password123');
  await expect(page).toHaveURL(/\/$/);
  const state = await context.storageState();
  await context.close();
  return state;
}

const test = base.extend<object, WorkerFixtures>({
  registrarState: [
    async ({ browser }, use) => {
      await use(await authenticatedState(browser, 'registrar@mema.ac.ke'));
    },
    { scope: 'worker' },
  ],
  context: async ({ browser, registrarState }, use) => {
    const context = await browser.newContext({ storageState: registrarState });
    await use(context);
    await context.close();
  },
});

test.describe('MOD-01-05 Student Recruitment, Applications & Admissions', () => {
  test('loads the admissions console and exports intake reports', async ({ page }) => {
    await page.goto('/admissions');
    await expect(page.getByTestId('admissions-console')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Admissions & applicant review' })).toBeVisible();
    await expect(page.getByTestId('application-row-APP-2026-00102')).toBeVisible();

    const pdfDownload = page.waitForEvent('download');
    await page.getByTestId('admissions-report-pdf').click();
    const pdf = await pdfDownload;
    expect(pdf.suggestedFilename()).toMatch(/admissions-intake-roll\.pdf$/);

    const csvDownload = page.waitForEvent('download');
    await page.getByTestId('admissions-fee-report-csv').click();
    const csv = await csvDownload;
    expect(csv.suggestedFilename()).toMatch(/application-fee-revenue\.csv$/);
  });

  test('imports a KUCCPS placement from the admin console', async ({ page }) => {
    test.setTimeout(90_000);
    const suffix = `${Date.now()}`.slice(-6);

    await page.goto('/admissions');
    await page.getByLabel('KUCCPS index').fill(`WEB${suffix}/2026`);
    await page.getByLabel('Applicant name').fill('Browser KUCCPS Applicant');
    await page.getByLabel('Programme code').fill('BSC-CS');
    await page.getByLabel('Mean grade').fill('B+');
    await page.getByTestId('import-kuccps').click();
    await expect(page.getByText('KUCCPS placement imported.')).toBeVisible({ timeout: 15_000 });
  });
});
