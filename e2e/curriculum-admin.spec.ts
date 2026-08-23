import { expect, test as base, type BrowserContext, type Page } from '@playwright/test';
import { AdminLoginPage } from './pages/admin-login-page';

type WorkerFixtures = {
  registrarState: Awaited<ReturnType<BrowserContext['storageState']>>;
  senateState: Awaited<ReturnType<BrowserContext['storageState']>>;
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

async function selectContaining(page: Page, label: string, text: string) {
  const select = page.getByLabel(label, { exact: true });
  const option = select.locator('option').filter({ hasText: text }).first();
  const value = await option.getAttribute('value');
  if (!value) throw new Error(`No ${label} option contained ${text}`);
  await select.selectOption(value);
}

const test = base.extend<object, WorkerFixtures>({
  registrarState: [
    async ({ browser }, use) => {
      await use(await authenticatedState(browser, 'senate@mema.ac.ke'));
    },
    { scope: 'worker' },
  ],
  senateState: [
    async ({ browser }, use) => {
      await use(await authenticatedState(browser, 'senate@mema.ac.ke'));
    },
    { scope: 'worker' },
  ],
  context: async ({ browser, registrarState }, use) => {
    const context = await browser.newContext({ storageState: registrarState });
    await use(context);
    await context.close();
  },
});

test.describe('MOD-01-03 Programme Structure & Curriculum Engine', () => {
  test.describe.configure({ mode: 'serial' });

  test.beforeEach(async ({ page }) => {
    page.on('pageerror', (error) => console.error(`Browser page error: ${error.message}`));
  });

  test('builds, validates, approves, locks and exports a curriculum on the web', async ({
    page,
    browser,
    senateState,
  }) => {
    test.setTimeout(120_000);
    const suffix = `${Date.now()}`.slice(-8);
    const programmeCode = `WEB-${suffix}`;
    const versionCode = `2026-W${suffix}`;

    await page.goto('/programmes');
    await expect(
      page.getByRole('heading', { name: 'Programme & curriculum engine' }),
    ).toBeVisible();
    await page.getByRole('button', { name: 'New programme' }).click();
    await selectContaining(page, 'Programme department', 'CS ·');
    await page.getByLabel('Programme code').fill(programmeCode);
    await page.getByLabel('Programme name').fill(`Browser Software Engineering ${suffix}`);
    await page.getByLabel('Programme graduation credits').fill('7');
    await page.getByLabel('Minimum residency credits').fill('7');
    await page.getByLabel('Qualification framework code').fill('KNQF-7');
    await page.getByLabel('Accreditation body').fill('CUE');
    await page.getByLabel('Accreditation reference').fill(`CUE-WEB-${suffix}`);
    await page.getByLabel('Accreditation expiry').fill('2029-12-31');
    await page.getByRole('button', { name: 'Create programme' }).click();
    await expect(
      page.getByText(`${programmeCode} created in the programme registry.`),
    ).toBeVisible();

    await page.getByLabel('Effective academic year').selectOption({ label: '2026/2027' });
    await page.getByLabel('Curriculum version code').fill(versionCode);
    await page.getByLabel('Version graduation credits').fill('7');
    await page.getByRole('button', { name: 'Create draft' }).click();
    await expect(page.getByText(`${versionCode} created as an editable draft.`)).toBeVisible();

    await selectContaining(page, 'Curriculum course', 'CSC 101');
    await page.getByRole('button', { name: 'Add', exact: true }).click();
    await expect(page.getByText('Course added to the semester grid.')).toBeVisible();
    await selectContaining(page, 'Curriculum course', 'CSC 102');
    await page.getByLabel('Course semester').fill('2');
    await page.getByRole('button', { name: 'Add', exact: true }).click();
    await expect(page.getByRole('cell', { name: 'CSC 102', exact: true })).toBeVisible();

    await page.getByLabel('Dependent course').selectOption({ label: 'CSC 102' });
    await page.getByLabel('Required course').selectOption({ label: 'CSC 101' });
    await page.getByRole('button', { name: 'Validate & add edge' }).click();
    await expect(page.getByText('Course dependency added to the validated graph.')).toBeVisible();

    await page.getByLabel('Dependent course').selectOption({ label: 'CSC 101' });
    await page.getByLabel('Required course').selectOption({ label: 'CSC 102' });
    await page.getByRole('button', { name: 'Validate & add edge' }).click();
    await expect(
      page.getByText('This prerequisite would create a cyclic dependency.'),
    ).toBeVisible();

    await page.getByRole('button', { name: 'Submit for review' }).click();
    await expect(page.getByText('Curriculum submitted for HOD review.')).toBeVisible();

    const senateContext = await browser.newContext({ storageState: senateState });
    const senatePage = await senateContext.newPage();
    senatePage.on('pageerror', (error) =>
      console.error(`Senate browser page error: ${error.message}`),
    );
    await senatePage.goto('/programmes');
    await senatePage.getByRole('button', { name: new RegExp(programmeCode) }).click();
    await senatePage.getByRole('button', { name: new RegExp(versionCode) }).click();

    for (const stage of ['HOD', 'DEAN', 'ACADEMIC BOARD', 'SENATE']) {
      await senatePage
        .getByLabel(new RegExp(`${stage.replace(' ', '_')} approval reference`))
        .fill(`${stage.replace(' ', '-')}-${suffix}`);
      await senatePage.getByRole('button', { name: `Approve ${stage}` }).click();
      await expect(senatePage.getByText(`${stage} approval recorded.`)).toBeVisible();
    }
    await expect(senatePage.getByText('Approved version locked')).toBeVisible();

    const csvDownload = senatePage.waitForEvent('download');
    await senatePage.getByRole('link', { name: 'CSV' }).click();
    await expect((await csvDownload).suggestedFilename()).toMatch(/^curriculum-.*\.csv$/);
    const pdfDownload = senatePage.waitForEvent('download');
    await senatePage.getByRole('link', { name: 'PDF' }).click();
    await expect((await pdfDownload).suggestedFilename()).toMatch(/^programme-handbook-.*\.pdf$/);
    await senateContext.close();
  });

  test('keeps the complete curriculum workspace usable on a mobile viewport', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/programmes');
    await expect(
      page.getByRole('heading', { name: 'Programme & curriculum engine' }),
    ).toBeVisible();
    await expect(page.getByRole('button', { name: 'New programme' })).toBeVisible();
    const overflows = await page.evaluate(
      () => document.documentElement.scrollWidth > document.documentElement.clientWidth,
    );
    expect(overflows).toBe(false);
  });
});
