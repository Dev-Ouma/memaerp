import { type Page } from '@playwright/test';

export class InstitutionMasterDataPage {
  constructor(readonly page: Page) {}

  async goto() { await this.page.goto('/institution'); }

  async createCampus(values: { code: string; name: string; town: string; resolution: string }) {
    await this.page.getByLabel('Campus code').fill(values.code);
    await this.page.getByLabel('Campus name').fill(values.name);
    await this.page.getByLabel('Campus town').fill(values.town);
    await this.page.getByLabel('Add campus approval reference').fill(values.resolution);
    await this.page.getByRole('button', { name: 'Add campus', exact: true }).click();
  }

  async createAcademicYear(values: { code: string; name: string; starts: string; ends: string }) {
    await this.page.getByLabel('Academic year code').fill(values.code);
    await this.page.getByLabel('Academic year name').fill(values.name);
    await this.page.getByLabel('Academic year starts on').fill(values.starts);
    await this.page.getByLabel('Academic year ends on').fill(values.ends);
    await this.page.getByRole('button', { name: 'Create draft year' }).click();
  }

  async createTerm(values: { yearCode: string; code: string; name: string; starts: string; ends: string }) {
    await this.page.getByLabel('Term academic year').selectOption({ label: values.yearCode });
    await this.page.getByLabel('Study mode', { exact: true }).selectOption('FULL_TIME');
    await this.page.getByLabel('Term code').fill(values.code);
    await this.page.getByLabel('Term name').fill(values.name);
    await this.page.getByLabel('Term starts on').fill(values.starts);
    await this.page.getByLabel('Term ends on').fill(values.ends);
    await this.page.getByRole('button', { name: 'Create draft term' }).click();
  }

  async createUnit(values: { departmentCode: string; code: string; name: string; resolution: string }) {
    const departmentSelect = this.page.getByLabel('Unit department');
    const departmentId = await departmentSelect.locator('option').filter({ hasText: new RegExp(`^${values.departmentCode} ·`) }).getAttribute('value');
    if (!departmentId) throw new Error(`Department ${values.departmentCode} was not available.`);
    await departmentSelect.selectOption(departmentId);
    await this.page.getByLabel('Unit code').fill(values.code);
    await this.page.getByLabel('Unit name').fill(values.name);
    await this.page.getByLabel('Add unit approval reference').fill(values.resolution);
    await this.page.getByRole('button', { name: 'Add unit', exact: true }).click();
  }

  async createIntake(values: { yearCode: string; code: string; name: string; opens: string; closes: string; reporting: string }) {
    await this.page.getByLabel('Intake academic year').selectOption({ label: values.yearCode });
    await this.page.getByLabel('Intake code').fill(values.code);
    await this.page.getByLabel('Intake name').fill(values.name);
    await this.page.getByLabel('Applications open').fill(values.opens);
    await this.page.getByLabel('Applications close').fill(values.closes);
    await this.page.getByLabel('Reporting date').fill(values.reporting);
    await this.page.getByRole('button', { name: 'Create intake' }).click();
  }
}
