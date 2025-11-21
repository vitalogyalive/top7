const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  page.on('console', msg => {
    console.log('Browser console:', msg.text());
  });

  try {
    // Login first
    console.log('Logging in...');
    await page.goto('http://localhost/login', { waitUntil: 'networkidle', timeout: 10000 });

    await page.fill('input[name="login"]', 'test2@topseven.fr');
    await page.fill('input[name="password"]', 'Passw0rd');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    // Navigate to records page
    console.log('Navigating to http://localhost/records...');
    await page.goto('http://localhost/records', { waitUntil: 'networkidle', timeout: 10000 });

    // Take a screenshot
    await page.screenshot({ path: 'records-page-logged-in.png', fullPage: true });
    console.log('Screenshot saved to records-page-logged-in.png');

    // Get the page text to check for error
    const bodyText = await page.locator('body').textContent();

    if (bodyText.includes('TOP7 - Error')) {
      console.log('ERROR FOUND: The page still shows the error message');
    } else {
      console.log('SUCCESS: No error message found on the page');
    }

    await page.waitForTimeout(2000);
  } catch (error) {
    console.error('Error:', error.message);
  }

  await browser.close();
})();
