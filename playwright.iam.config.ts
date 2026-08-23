import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  testMatch: /iam-admin\.spec\.ts/,
  fullyParallel: true,
  workers: 4,
  timeout: 60_000,
  retries: 0,
  expect: { timeout: 15_000 },
  reporter: [['list'], ['html', { outputFolder: 'playwright-report/iam', open: 'never' }]],
  use: {
    baseURL: 'http://localhost:3015',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
});
