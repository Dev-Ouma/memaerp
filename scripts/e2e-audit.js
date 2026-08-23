const { chromium } = require('playwright');

async function runE2eAudit() {
  console.log('🚀 Running Comprehensive Playwright E2E & Accessibility Audit for MEMA ERP...\n');

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1280, height: 800 },
  });

  const apps = [
    { name: 'Public Website', url: 'http://localhost:3000', expectedTitle: 'Mema University' },
    { name: 'Applicant Portal', url: 'http://localhost:3001', expectedTitle: 'Admissions' },
    { name: 'Applicant Status', url: 'http://localhost:3001/status', expectedTitle: 'Admissions' },
    { name: 'Student Portal', url: 'http://localhost:3002', expectedTitle: 'Student Portal' },
    { name: 'Lecturer Portal', url: 'http://localhost:3003', expectedTitle: 'Lecturer Portal' },
    { name: 'Staff Portal', url: 'http://localhost:3004', expectedTitle: 'Staff Portal' },
    { name: 'ERP Admin Console', url: 'http://localhost:3005', expectedTitle: 'Enterprise Administration' },
  ];

  let passCount = 0;
  let totalChecks = 0;

  for (const app of apps) {
    console.log(`--------------------------------------------------`);
    console.log(`🔍 Testing: ${app.name} (${app.url})`);
    const page = await context.newPage();

    try {
      totalChecks++;
      const response = await page.goto(app.url, { waitUntil: 'domcontentloaded', timeout: 10000 });
      const status = response ? response.status() : 0;

      if (status === 200) {
        console.log(`  ✅ HTTP 200 OK`);
        passCount++;
      } else {
        console.log(`  ❌ HTTP Status Error: ${status}`);
      }

      // Title check
      totalChecks++;
      const title = await page.title();
      if (title.includes(app.expectedTitle)) {
        console.log(`  ✅ Page Title Match: "${title}"`);
        passCount++;
      } else {
        console.log(`  ⚠️ Page Title Mismatch: Got "${title}", expected "${app.expectedTitle}"`);
      }

      // Accessibility / Heading check
      totalChecks++;
      const hCount = await page.locator('h1, h2, h3').count();
      if (hCount > 0) {
        const firstHeader = await page.locator('h1, h2, h3').first().innerText();
        console.log(`  ✅ Semantic Heading Verified: "${firstHeader.trim()}"`);
        passCount++;
      } else {
        console.log(`  ❌ No heading elements found for accessibility screen readers.`);
      }

      // Clickable Elements check
      totalChecks++;
      const buttonCount = await page.locator('button, a[href]').count();
      if (buttonCount > 0) {
        console.log(`  ✅ Interactive Elements: ${buttonCount} clickable targets found.`);
        passCount++;
      } else {
        console.log(`  ❌ No interactive buttons or links detected.`);
      }

    } catch (err) {
      console.log(`  ❌ Error loading page: ${err.message}`);
    } finally {
      await page.close();
    }
  }

  // Interactive flow checks
  console.log(`--------------------------------------------------`);
  console.log(`🧪 Testing Sub-Routes & Portal Workflows...`);

  const subRoutes = [
    { name: 'Student Registration', url: 'http://localhost:3002/registration' },
    { name: 'Student Finance & M-Pesa', url: 'http://localhost:3002/finance' },
    { name: 'Student Results', url: 'http://localhost:3002/results' },
    { name: 'Admin Student Directory', url: 'http://localhost:3005/students' },
  ];

  for (const sr of subRoutes) {
    totalChecks++;
    const subPage = await context.newPage();
    try {
      const res = await subPage.goto(sr.url, { waitUntil: 'domcontentloaded' });
      if (res && res.status() === 200) {
        console.log(`  ✅ Route Passed (${sr.name}): HTTP 200 OK`);
        passCount++;
      } else {
        console.log(`  ❌ Route Failed (${sr.name})`);
      }
    } catch (err) {
      console.log(`  ❌ Route Error (${sr.name}): ${err.message}`);
    } finally {
      await subPage.close();
    }
  }

  await browser.close();

  console.log(`\n==================================================`);
  console.log(`🏁 E2E Audit Score: ${passCount} / ${totalChecks} Checks Passed (${Math.round((passCount/totalChecks)*100)}%)`);
  console.log(`==================================================\n`);
}

runE2eAudit();
