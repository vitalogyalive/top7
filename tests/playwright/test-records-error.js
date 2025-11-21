const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  page.on('console', msg => {
    console.log('Browser console:', msg.text());
  });

  try {
    console.log('Navigating to http://localhost/records...');
    await page.goto('http://localhost/records', { waitUntil: 'networkidle', timeout: 10000 });

    // Take a screenshot
    await page.screenshot({ path: 'records-page-error.png', fullPage: true });
    console.log('Screenshot saved to records-page-error.png');

    // Get the page text to see the error
    const bodyText = await page.locator('body').textContent();
    console.log('Page content preview:', bodyText.substring(0, 500));

    await page.waitForTimeout(2000);
  } catch (error) {
    console.error('Error:', error.message);
  }

  await browser.close();
})();
