import { expect, test as base, type BrowserContext } from '@playwright/test';
import { AdminLoginPage } from './pages/admin-login-page';
import { InstitutionMasterDataPage } from './pages/institution-master-data-page';

async function login(page: import('@playwright/test').Page) {
  const signIn = new AdminLoginPage(page);
  await signIn.goto();
  await signIn.login('admin@mema.ac.ke', 'password123');
  await expect(page).toHaveURL(/\/$/);
}

type WorkerFixtures = {
  adminStorageState: Awaited<ReturnType<BrowserContext['storageState']>>;
};

const test = base.extend<object, WorkerFixtures>({
  adminStorageState: [async ({ browser }, use) => {
    const context = await browser.newContext();
    const page = await context.newPage();
    await login(page);
    await use(await context.storageState());
    await context.close();
  }, { scope: 'worker' }],
  context: async ({ browser, adminStorageState }, use) => {
    const context = await browser.newContext({ storageState: adminStorageState });
    await use(context);
    await context.close();
  },
});

test.describe('MOD-01-02 Institutional Administration & Master Data', () => {
  test('renders live hierarchy, calendar and cached lookup data', async ({ page }) => {
    const institution = new InstitutionMasterDataPage(page);
    await institution.goto();
    await expect(page.getByRole('heading', { name: 'Institution & Master Data' })).toBeVisible();
    await expect(page.getByText('Department of Computer Science', { exact: true })).toBeVisible();
    await expect(page.getByRole('region', { name: 'Academic year 2026/2027' })).toBeVisible();
    await expect(page.getByText('KE · Kenyan')).toBeVisible();
  });

  test('creates an approved campus through the operator workflow', async ({ page }) => {
    const institution = new InstitutionMasterDataPage(page);
    await institution.goto();
    const suffix = `${Date.now()}${test.info().workerIndex}`.slice(-8);
    const code = `W${suffix}`.slice(0, 10);
    await institution.createCampus({ code, name: `Web Campus ${suffix}`, town: 'Nakuru', resolution: `COUNCIL-${suffix}` });
    await expect(page.getByText('Campus created and added to the institutional hierarchy.')).toBeVisible();
    await expect(page.getByLabel('Faculty campus').locator(`option[value]`).filter({ hasText: code })).toHaveCount(1);
  });

  test('creates, approves and activates a new year and term', async ({ page }) => {
    const institution = new InstitutionMasterDataPage(page);
    await institution.goto();
    const suffix = `${Date.now()}${test.info().workerIndex}`.slice(-6);
    const yearCode = `2035/${suffix}`.slice(0, 16);
    const termCode = `${yearCode}-S1`.slice(0, 16);
    await institution.createAcademicYear({ code: yearCode, name: `Academic Year ${yearCode}`, starts: '2035-09-01', ends: '2036-08-31' });
    await expect(page.getByText('Academic year created in draft.')).toBeVisible();
    await institution.createTerm({ yearCode, code: termCode, name: 'Semester 1', starts: '2035-09-08', ends: '2035-12-19' });
    await expect(page.getByText('Academic term created in draft.')).toBeVisible();
    await page.getByLabel(`Senate resolution for ${yearCode}`).fill(`SENATE-${suffix}`);
    await page.getByRole('button', { name: `Activate academic year ${yearCode}` }).click();
    await expect(page.getByText(`${yearCode} activated and published.`)).toBeVisible();
    await page.getByRole('button', { name: `Activate term ${termCode}` }).click();
    await expect(page.getByText(`${termCode} activated.`)).toBeVisible();

    await page.getByLabel('Senate resolution for 2026/2027').fill(`SENATE-RESTORE-${suffix}`);
    await page.getByRole('button', { name: 'Activate academic year 2026/2027' }).click();
    await expect(page.getByText('2026/2027 activated and published.')).toBeVisible();
    await page.getByRole('button', { name: 'Activate term 2026/2027-S1' }).click();
    await expect(page.getByText('2026/2027-S1 activated.')).toBeVisible();
  });

  test('adds a lookup value and refreshes the cached directory', async ({ page }) => {
    const institution = new InstitutionMasterDataPage(page);
    await institution.goto();
    const suffix = `${Date.now()}${test.info().workerIndex}`.slice(-6);
    await page.getByLabel('Lookup code').fill(`X${suffix}`);
    await page.getByLabel('Lookup name').fill(`Browser nationality ${suffix}`);
    await page.getByRole('button', { name: 'Add value' }).click();
    await expect(page.getByText('Master lookup value created and cache invalidated.')).toBeVisible();
    await expect(page.getByText(`X${suffix} · Browser nationality ${suffix}`)).toBeVisible();
  });

  test('creates and archives a governed department unit', async ({ page }) => {
    const institution = new InstitutionMasterDataPage(page);
    await institution.goto();
    const suffix = `${Date.now()}${test.info().workerIndex}`.slice(-6);
    const code = `U${suffix}`;
    await institution.createUnit({ departmentCode: 'CS', code, name: `Browser Lab ${suffix}`, resolution: `SENATE-${suffix}` });
    await expect(page.getByText('Unit created and linked to its department.')).toBeVisible();
    await expect(page.getByText(`↳ Browser Lab ${suffix}`)).toBeVisible();
    await page.getByRole('button', { name: `Archive unit ${code}` }).click();
    await expect(page.getByText(`${code} archived.`)).toBeVisible();
  });

  test('publishes an intake and critical calendar deadline', async ({ page }) => {
    const institution = new InstitutionMasterDataPage(page);
    await institution.goto();
    const suffix = `${Date.now()}${test.info().workerIndex}`.slice(-6);
    await institution.createIntake({
      yearCode: '2026/2027', code: `JAN-${suffix}`, name: `Browser Intake ${suffix}`,
      opens: '2026-09-01', closes: '2026-11-30', reporting: '2027-01-05',
    });
    await expect(page.getByText('Intake window created.')).toBeVisible();
    await expect(page.getByText(`Browser Intake ${suffix}`)).toBeVisible();

    await page.getByLabel('Event academic year').selectOption({ label: '2026/2027' });
    await page.getByLabel('Calendar event title').fill(`Browser Deadline ${suffix}`);
    await page.getByLabel('Starts at').fill('2026-10-16T17:00');
    await page.getByLabel('Critical deadline').check();
    await page.getByRole('button', { name: 'Publish event' }).click();
    await expect(page.getByText('Calendar event published.')).toBeVisible();
    await expect(page.getByText(`Browser Deadline ${suffix}`)).toBeVisible();
  });

  test('downloads official directory and calendar PDF reports', async ({ page }) => {
    const institution = new InstitutionMasterDataPage(page);
    await institution.goto();
    const directoryDownload = page.waitForEvent('download');
    await page.getByRole('link', { name: 'Directory PDF' }).click();
    await expect((await directoryDownload).suggestedFilename()).toBe('institutional-directory.pdf');

    const calendarDownload = page.waitForEvent('download');
    await page.getByRole('link', { name: 'Calendar PDF' }).click();
    await expect((await calendarDownload).suggestedFilename()).toMatch(/^academic-calendar-.+\.pdf$/);
  });

  test('remains usable without horizontal page overflow on a mobile viewport', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    const institution = new InstitutionMasterDataPage(page);
    await institution.goto();
    await expect(page.getByRole('heading', { name: 'Institution & Master Data' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Refresh' })).toBeVisible();
    const overflows = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth);
    expect(overflows).toBe(false);
  });
});
