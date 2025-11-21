const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1280, height: 900 }
  });
  const page = await context.newPage();

  try {
    // Step 1: Navigate to the application
    console.log('Navigating to application...');
    await page.goto('http://localhost', { waitUntil: 'networkidle' });
    await page.screenshot({ path: 'test-screenshots/01-homepage.png', fullPage: true });
    console.log('Screenshot saved: 01-homepage.png');

    // Check input field sizes
    const loginInput = await page.$('input[name="login"]');
    const passwordInput = await page.$('input[name="password"]');

    if (loginInput && passwordInput) {
      const loginBox = await loginInput.boundingBox();
      const passwordBox = await passwordInput.boundingBox();
      console.log(`Login input size: ${loginBox.width}x${loginBox.height}`);
      console.log(`Password input size: ${passwordBox.width}x${passwordBox.height}`);

      if (loginBox.height < 40) {
        console.log('WARNING: Login input height is too small!');
      } else {
        console.log('OK: Input fields are properly sized');
      }
    }

    // Step 2: Login with admin credentials
    console.log('Attempting login...');

    // Fill in login form - use testuser1 email to see chart data
    await page.fill('input[name="login"]', 'test1@topseven.fr');
    await page.fill('input[name="password"]', 'test123');
    await page.screenshot({ path: 'test-screenshots/02-login-filled.png', fullPage: true });
    console.log('Screenshot saved: 02-login-filled.png');

    // Submit the form and wait for navigation
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.evaluate(() => {
        document.querySelector('form').submit();
      })
    ]);
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'test-screenshots/03-after-login.png', fullPage: true });
    console.log('Screenshot saved: 03-after-login.png');
    console.log('Current URL after login:', page.url());

    // Step 3: Click on STATS Inter-EQUIPES button
    console.log('Clicking on STATS Inter-EQUIPES...');
    const statsButton = await page.$('a:has-text("STATS Inter-EQUIPES")');
    if (statsButton) {
      await statsButton.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: 'test-screenshots/04-stats-inter-equipes.png', fullPage: true });
      console.log('Screenshot saved: 04-stats-inter-equipes.png');
    } else {
      console.log('STATS button not found, navigating directly...');
    }

    // Step 4: Navigate to stats_graphs.php
    console.log('Navigating to stats graphs page...');
    await page.goto('http://localhost/stats_graphs.php', { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000); // Wait longer for charts to load

    // Test the API directly
    const apiResponse = await page.evaluate(async () => {
      try {
        const response = await fetch('/stats_api.php?action=player_evolution');
        const text = await response.text();
        return text.substring(0, 500);
      } catch (e) {
        return 'Error: ' + e.message;
      }
    });
    console.log('API Response (evolution):', apiResponse);

    // Test players_list API
    const playersListResponse = await page.evaluate(async () => {
      try {
        const response = await fetch('/stats_api.php?action=players_list');
        const text = await response.text();
        return text.substring(0, 500);
      } catch (e) {
        return 'Error: ' + e.message;
      }
    });
    console.log('API Response (players_list):', playersListResponse);

    await page.screenshot({ path: 'test-screenshots/05-stats-graphs.png', fullPage: true });
    console.log('Screenshot saved: 05-stats-graphs.png');

    // Click on Comparaison joueurs tab
    console.log('Clicking on Comparaison joueurs tab...');

    // Capture console errors
    page.on('console', msg => {
      if (msg.type() === 'error') {
        console.log('Browser console error:', msg.text());
      }
    });

    await page.click('button:has-text("Comparaison joueurs")');
    await page.waitForTimeout(3000);

    // Check HTML content of players-checkboxes div
    const checkboxesHtml = await page.$eval('#players-checkboxes', el => el.innerHTML);
    console.log('Checkboxes div content:', checkboxesHtml.substring(0, 500));

    await page.screenshot({ path: 'test-screenshots/06-comparison-tab.png', fullPage: true });
    console.log('Screenshot saved: 06-comparison-tab.png');

    // Check for player checkboxes
    const checkboxes = await page.$$('.player-checkbox');
    console.log(`Found ${checkboxes.length} player checkboxes`);

    // Check for canvas elements (charts)
    const canvasElements = await page.$$('canvas');
    console.log(`Found ${canvasElements.length} canvas elements (charts)`);

    // Step 5: Navigate to main stats page
    console.log('Navigating to main stats page...');
    await page.goto('http://localhost/stats.php', { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'test-screenshots/06-stats-main.png', fullPage: true });
    console.log('Screenshot saved: 06-stats-main.png');

    // List any graph-related elements
    const graphElements = await page.$$('canvas, .chart, svg[class*="chart"]');
    console.log(`Found ${graphElements.length} graph elements on stats page`);

    console.log('\nTest completed successfully!');

  } catch (error) {
    console.error('Test error:', error.message);
    await page.screenshot({ path: 'test-screenshots/error.png' });
    console.log('Error screenshot saved');
  } finally {
    await browser.close();
  }
})();
