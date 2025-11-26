const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    // Set up error listeners
    page.on('console', msg => console.log('Browser console:', msg.type(), msg.text()));
    page.on('pageerror', error => console.log('Page error:', error.message));
    page.on('response', response => {
      if (response.status() >= 400) {
        console.log('HTTP Error:', response.status(), response.url());
      }
    });

    console.log('Navigating to login page...');
    await page.goto('http://localhost');

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Take screenshot of login page
    await page.screenshot({ path: 'login-before.png' });
    console.log('Screenshot saved: login-before.png');

    // Fill in login credentials
    console.log('Filling login form...');
    await page.fill('input[name="login"]', 'admin');
    await page.fill('input[name="password"]', 'admin');

    // Click login button
    console.log('Clicking login button...');
    await page.click('button[type="submit"]');

    // Wait a bit for the response
    await page.waitForTimeout(2000);

    // Take screenshot after login attempt
    await page.screenshot({ path: 'after-login.png' });
    console.log('Screenshot saved: after-login.png');

    // Check current URL
    console.log('Current URL:', page.url());

    // Get page content
    const bodyText = await page.textContent('body');
    console.log('Page content preview:', bodyText.substring(0, 500));

    // Check if there's an error message visible
    const errorElements = await page.$$('.error, .alert, [class*="error"]');
    console.log('Found error elements:', errorElements.length);

    // Get page title
    const title = await page.title();
    console.log('Page title:', title);

    // Wait a bit to see what happens
    await page.waitForTimeout(3000);

  } catch (error) {
    console.error('Test error:', error);
  } finally {
    await browser.close();
  }
})();
