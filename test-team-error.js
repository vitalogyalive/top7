const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  // Listen for console messages to capture PHP warnings/errors
  page.on('console', msg => {
    console.log('Browser console:', msg.text());
  });

  try {
    console.log('Navigating to http://localhost/team...');
    await page.goto('http://localhost/team', { waitUntil: 'networkidle', timeout: 10000 });

    // Take a screenshot
    await page.screenshot({ path: 'team-page-error.png', fullPage: true });
    console.log('Screenshot saved to team-page-error.png');

    // Get the page content to see if error is visible
    const content = await page.content();
    if (content.includes('Warning:')) {
      console.log('PHP Warning detected in page content');
    }

    await page.waitForTimeout(2000);
  } catch (error) {
    console.error('Error:', error.message);
  }

  await browser.close();
})();
