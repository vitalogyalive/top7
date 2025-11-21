const { chromium } = require('playwright');

/**
 * Enhanced login test with thorough verification
 * Tests that login actually works and session is maintained
 */

async function testLoginAndSession() {
  console.log('========================================');
  console.log('Login & Session Verification Test');
  console.log('========================================\n');

  const browser = await chromium.launch({
    headless: false,
    slowMo: 100
  });

  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });

  const page = await context.newPage();

  // Track console errors
  const browserErrors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      browserErrors.push(msg.text());
      console.log('  Browser Error:', msg.text());
    }
  });

  try {
    console.log('Step 1: Navigate to login page');
    await page.goto('http://localhost/login', {
      waitUntil: 'networkidle',
      timeout: 10000
    });
    console.log('✓ Login page loaded\n');

    // Take screenshot before login
    await page.screenshot({ path: 'test-screenshots/1-before-login.png' });

    console.log('Step 2: Fill in credentials');
    await page.fill('input[name="login"]', 'test2@topseven.fr');
    await page.fill('input[name="password"]', 'Passw0rd');
    console.log('✓ Credentials filled\n');

    console.log('Step 3: Submit login form');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    // Take screenshot after login
    await page.screenshot({ path: 'test-screenshots/2-after-login.png' });

    // Check URL to verify redirect
    const currentUrl = page.url();
    console.log('Current URL:', currentUrl);

    if (currentUrl.includes('login')) {
      console.log('✗ FAILED: Still on login page - Login did not succeed\n');

      // Check for error messages
      const pageText = await page.locator('body').textContent();
      if (pageText.includes('Invalid') || pageText.includes('incorrect')) {
        console.log('  Found error message on page');
      }

      await browser.close();
      process.exit(1);
    }

    console.log('✓ Redirected away from login page\n');

    console.log('Step 4: Check for logged-in user indicators');

    // Try to find user-specific elements
    const bodyText = await page.locator('body').textContent();

    if (bodyText.includes('testuser2') || bodyText.includes('test2@topseven.fr')) {
      console.log('✓ Found username/email in page - User is logged in\n');
    } else {
      console.log('⚠ Warning: Could not find username in page content\n');
    }

    // Check for logout link
    const hasLogout = bodyText.toLowerCase().includes('logout') ||
                     bodyText.toLowerCase().includes('déconnexion');
    if (hasLogout) {
      console.log('✓ Found logout option - Session is active\n');
    } else {
      console.log('⚠ Warning: No logout option found\n');
    }

    console.log('Step 5: Test session persistence - Navigate to protected page');
    await page.goto('http://localhost/player', {
      waitUntil: 'networkidle',
      timeout: 10000
    });
    await page.screenshot({ path: 'test-screenshots/3-player-page.png' });

    const playerUrl = page.url();
    console.log('Player page URL:', playerUrl);

    if (playerUrl.includes('login')) {
      console.log('✗ FAILED: Redirected to login - Session was lost\n');
      await browser.close();
      process.exit(1);
    }

    console.log('✓ Session maintained - Can access protected pages\n');

    // Check for errors on player page
    const playerPageText = await page.locator('body').textContent();
    console.log('Step 6: Check player page for errors');

    const errors = [];
    if (playerPageText.includes('Warning:')) errors.push('PHP Warning');
    if (playerPageText.includes('Fatal error:')) errors.push('PHP Fatal Error');
    if (playerPageText.includes('TOP7 - Error')) errors.push('Application Error');
    if (playerPageText.includes('SQLSTATE')) errors.push('SQL Error');

    if (errors.length > 0) {
      console.log('⚠ Errors found on player page:', errors.join(', '));
      console.log('  (This means login worked, but the page has bugs)\n');
    } else {
      console.log('✓ No errors on player page\n');
    }

    console.log('Step 7: Test another protected page (team)');
    await page.goto('http://localhost/team', {
      waitUntil: 'networkidle',
      timeout: 10000
    });
    await page.screenshot({ path: 'test-screenshots/4-team-page.png' });

    const teamUrl = page.url();
    console.log('Team page URL:', teamUrl);

    if (teamUrl.includes('login')) {
      console.log('✗ FAILED: Redirected to login on team page\n');
      await browser.close();
      process.exit(1);
    }

    console.log('✓ Session still active on team page\n');

    // Check team page for errors
    const teamPageText = await page.locator('body').textContent();
    const teamErrors = [];
    if (teamPageText.includes('Warning:')) teamErrors.push('PHP Warning');
    if (teamPageText.includes('Fatal error:')) teamErrors.push('PHP Fatal Error');
    if (teamPageText.includes('TOP7 - Error')) teamErrors.push('Application Error');
    if (teamPageText.includes('SQLSTATE')) teamErrors.push('SQL Error');

    if (teamErrors.length > 0) {
      console.log('⚠ Errors found on team page:', teamErrors.join(', '));
    } else {
      console.log('✓ No errors on team page\n');
    }

    console.log('Step 8: Test records page (previously had SQL error)');
    await page.goto('http://localhost/records', {
      waitUntil: 'networkidle',
      timeout: 10000
    });
    await page.screenshot({ path: 'test-screenshots/5-records-page.png' });

    const recordsPageText = await page.locator('body').textContent();
    const recordsErrors = [];
    if (recordsPageText.includes('Warning:')) recordsErrors.push('PHP Warning');
    if (recordsPageText.includes('Fatal error:')) recordsErrors.push('PHP Fatal Error');
    if (recordsPageText.includes('TOP7 - Error')) recordsErrors.push('Application Error');
    if (recordsPageText.includes('SQLSTATE')) recordsErrors.push('SQL Error');

    if (recordsErrors.length > 0) {
      console.log('⚠ Errors found on records page:', recordsErrors.join(', '));
      console.log('  Records page may need additional fixes\n');
    } else {
      console.log('✓ Records page working correctly (SQL error was fixed!)\n');
    }

    // Final summary
    console.log('========================================');
    console.log('LOGIN VERIFICATION SUMMARY');
    console.log('========================================');
    console.log('✓ Login form submission: SUCCESS');
    console.log('✓ Redirect after login: SUCCESS');
    console.log('✓ Session persistence: SUCCESS');
    console.log('✓ Access to protected pages: SUCCESS');
    console.log('');
    console.log('CONCLUSION: Login is working correctly!');
    console.log('');

    if (errors.length > 0 || teamErrors.length > 0 || recordsErrors.length > 0) {
      console.log('Note: Some pages have errors, but this is due to bugs');
      console.log('in those pages, NOT a login/session issue.');
    }

    console.log('========================================\n');

    if (browserErrors.length > 0) {
      console.log('Browser console errors:', browserErrors.length);
    }

  } catch (error) {
    console.error('\n✗ Test failed with exception:', error.message);
    await page.screenshot({ path: 'test-screenshots/error-state.png' });
    await browser.close();
    process.exit(1);
  }

  await browser.close();
  console.log('Test completed successfully!\n');
}

// Run the test
testLoginAndSession().catch(error => {
  console.error('Fatal error:', error);
  process.exit(1);
});
