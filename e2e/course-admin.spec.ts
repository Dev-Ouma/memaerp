import { expect, test as base, type BrowserContext, type Page } from '@playwright/test';
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

test.describe('MOD-01-04 Course Master Catalogue & Semester Offerings', () => {
  test.describe.configure({ mode: 'serial' });

  test('creates, approves, offers and exports a course on the web', async ({ page }) => {
    test.setTimeout(120_000);
    const suffix = `${Date.now()}`.slice(-8);
    const courseCode = `WEB ${suffix}`.slice(0, 20);

    await page.goto('/courses');
    await expect(page.getByRole('heading', { name: 'Course catalogue & semester offerings' })).toBeVisible();
    await page.getByRole('button', { name: 'New course' }).click();
    await selectContaining(page, 'Course department', 'CS ·');
    await page.getByLabel('Course code').fill(courseCode);
    await page.getByLabel('Course title').fill(`Browser Networks ${suffix}`);
    await page.getByLabel('Course credits').fill('3');
    await page.getByLabel('Learning outcomes').fill('Describe packet switching.');
    await page.getByLabel('Syllabus outline').fill('OSI model and routing.');
    await page.getByRole('button', { name: 'Create draft' }).click();
    await expect(page.getByText('Course drafted for departmental review.')).toBeVisible();

    await selectContaining(page, 'Required course', 'CSC 101');
    await page.getByRole('button', { name: 'Validate & add edge' }).click();
    await expect(page.getByText('Course dependency added to the validated graph.')).toBeVisible();

    await page.getByRole('button', { name: 'Submit for review' }).click();
    await expect(page.getByText('Course submitted for department board review.')).toBeVisible();

    for (const stage of ['DEPARTMENT BOARD', 'SCHOOL BOARD']) {
      await page.getByLabel(new RegExp(`${stage.replace(' ', '_')} approval reference`)).fill(`${stage}-${suffix}`);
      await page.getByRole('button', { name: `Approve ${stage}` }).click();
      await expect(page.getByText(`${stage} approval recorded.`)).toBeVisible();
    }
    await expect(page.getByText('Published catalogue course')).toBeVisible();

    await selectContaining(page, 'Offering term', '2026/2027');
    await selectContaining(page, 'Offering campus', 'MAIN');
    await page.getByLabel('Section code').fill('W');
    await page.getByLabel('Section capacity').fill('40');
    await page.getByRole('button', { name: 'Open section' }).click();
    await expect(page.getByText('Semester offering opened.')).toBeVisible();
    await expect(page.getByRole('cell', { name: 'W', exact: true })).toBeVisible();

    await selectContaining(page, 'Lecturer for section W', 'Miriam');
    await page.getByRole('button', { name: 'Assign' }).click();
    await expect(page.getByText('Lecturer allocated to the section.')).toBeVisible();

    const csvDownload = page.waitForEvent('download');
    await page.getByRole('link', { name: 'Sections CSV' }).click();
    await expect((await csvDownload).suggestedFilename()).toMatch(/semester-sections\.csv$/);
    const pdfDownload = page.waitForEvent('download');
    await page.getByRole('link', { name: 'Catalogue PDF' }).click();
    await expect((await pdfDownload).suggestedFilename()).toMatch(/course-catalogue\.pdf$/);
  });

  test('rejects a cyclic catalogue prerequisite in the browser', async ({ page }) => {
    await page.goto('/courses');
    await page.getByRole('button', { name: /CSC 101/ }).click();
    await selectContaining(page, 'Required course', 'CSC 102');
    await page.getByRole('button', { name: 'Validate & add edge' }).click();
    await expect(page.getByText('This prerequisite would create a cyclic dependency.')).toBeVisible();
  });

  test('keeps the catalogue workspace usable on a mobile viewport', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto('/courses');
    await expect(page.getByRole('heading', { name: 'Course catalogue & semester offerings' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'New course' })).toBeVisible();
    const overflows = await page.evaluate(
      () => document.documentElement.scrollWidth > document.documentElement.clientWidth,
    );
    expect(overflows).toBe(false);
  });
});
