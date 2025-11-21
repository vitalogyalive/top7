# Playwright Testing Guide for TOP7

This guide explains how to test the TOP7 application using Playwright for automated browser testing.

## Prerequisites

- Node.js installed on your system
- Docker containers running (web, database)
- Application accessible at `http://localhost`

## Installation

Playwright can be installed and run without adding it to your project dependencies using `npx`:

```bash
npx -y playwright install chromium
```

This command:
- Downloads and installs Playwright on-demand
- Installs the Chromium browser
- Doesn't modify your `package.json`

## Basic Test Structure

Create a JavaScript file (e.g., `test-example.js`) with this structure:

```javascript
const { chromium } = require('playwright');

(async () => {
  // Launch browser
  const browser = await chromium.launch({
    headless: false  // Set to true for CI/CD
  });
  const context = await browser.newContext();
  const page = await context.newPage();

  // Listen for console messages
  page.on('console', msg => {
    console.log('Browser console:', msg.text());
  });

  try {
    // Your test code here
    await page.goto('http://localhost/page', {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    // Take screenshot
    await page.screenshot({ path: 'screenshot.png', fullPage: true });

  } catch (error) {
    console.error('Error:', error.message);
  }

  await browser.close();
})();
```

## Running Tests

Execute your test with Node:

```bash
node test-example.js
```

## Common Test Patterns

### 1. Testing Unauthenticated Pages

Check pages that require login redirect properly:

```javascript
await page.goto('http://localhost/team', { waitUntil: 'networkidle' });
await page.screenshot({ path: 'team-page.png', fullPage: true });

// Check if redirected to login
const url = page.url();
if (url.includes('login')) {
  console.log('Correctly redirected to login');
}
```

### 2. Testing with Authentication

Login first, then test protected pages:

```javascript
// Login
await page.goto('http://localhost/login', { waitUntil: 'networkidle' });
await page.fill('input[name="login"]', 'test2@topseven.fr');
await page.fill('input[name="password"]', 'Passw0rd');
await page.click('button[type="submit"]');
await page.waitForTimeout(2000);

// Navigate to protected page
await page.goto('http://localhost/records', { waitUntil: 'networkidle' });
```

### 3. Checking for PHP Errors

Check if the page contains error messages:

```javascript
const bodyText = await page.locator('body').textContent();

if (bodyText.includes('Warning:') || bodyText.includes('Error')) {
  console.log('ERROR: PHP error detected on page');
} else {
  console.log('SUCCESS: No errors found');
}
```

### 4. Taking Screenshots

Capture the page state for visual verification:

```javascript
// Full page screenshot
await page.screenshot({ path: 'full-page.png', fullPage: true });

// Viewport only
await page.screenshot({ path: 'viewport.png' });

// Specific element
await page.locator('#palmares').screenshot({ path: 'element.png' });
```

### 5. Interacting with Elements

```javascript
// Fill form fields
await page.fill('input[name="username"]', 'testuser');

// Click buttons
await page.click('button[type="submit"]');

// Select dropdown
await page.selectOption('select[name="display_stats"]', '2');

// Check checkbox
await page.check('input[type="checkbox"]');

// Wait for element
await page.waitForSelector('.player-stats');
```

## Comprehensive Test Suite

### Testing All Pages (`test-all-pages.js`)

The comprehensive test suite automatically tests all pages in the application, both public and authenticated. It generates a detailed HTML report with screenshots.

**Features:**
- Tests all public pages (login, register, intro, etc.)
- Tests all authenticated pages after logging in
- Checks for PHP errors, warnings, SQL errors
- Takes screenshots of every page
- Measures load times
- Generates an HTML report with results
- Color-coded status (passed, warning, failed)

**Run the comprehensive test:**
```bash
node test-all-pages.js
```

**What it tests:**

Public Pages:
- Home page (/)
- Login page
- Registration page
- Password reset
- Introduction page

Authenticated Pages (requires login):
- Player profile
- Team management
- Pronostics
- Rankings
- Records
- Statistics and graphs
- Calendar
- Team agenda
- Parameters
- And more...

**Output:**
- Console output with real-time results
- Screenshots saved to `test-screenshots/` directory
- HTML report at `test-screenshots/report.html`

**Report includes:**
- Summary cards (total, passed, warnings, failed)
- Failed tests with detailed errors
- Complete test results table with:
  - Test status (passed/failed/warning)
  - Page name and path
  - Authentication status
  - HTTP status code
  - Load time in milliseconds
  - Link to screenshot

**Error Detection:**
The test automatically checks for:
- `TOP7 - Error` messages
- PHP Warnings
- PHP Fatal Errors
- PHP Notices
- SQL errors (SQLSTATE)
- Undefined variables
- Undefined function calls

**Example output:**
```
========================================
TOP7 Application Test Suite
========================================

=== Testing Public Pages ===
Testing: Home (index) (http://localhost/)
  ✓ Status: passed (200) - 1234ms
Testing: Login (http://localhost/login)
  ✓ Status: passed (200) - 567ms

=== Logging in ===
✓ Login successful

=== Testing Authenticated Pages ===
Testing: Player Profile (http://localhost/player)
  ✓ Status: passed (200) - 890ms
Testing: Records (http://localhost/records)
  ✓ Status: passed (200) - 1456ms

========================================
Test Summary
========================================
Total:    19
Passed:   18 ✓
Warnings: 0 ⚠
Failed:   1 ✗
========================================
```

**Viewing the report:**
```bash
# Open the HTML report in your browser
# On WSL/Linux
xdg-open test-screenshots/report.html

# On Windows
start test-screenshots/report.html

# On Mac
open test-screenshots/report.html
```

**Customizing the test:**

Edit `test-all-pages.js` to:
- Add/remove pages to test
- Change test credentials
- Adjust timeouts
- Modify error detection rules

```javascript
// Add a new page to test
const PAGES = {
  authenticated: [
    { path: '/your-new-page', name: 'Your New Page' },
    // ... other pages
  ]
};
```

## Example Tests in This Project

### 1. Test Team Page (`test-team-error.js`)

Tests the team page for PHP warnings:

```javascript
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  page.on('console', msg => {
    console.log('Browser console:', msg.text());
  });

  try {
    console.log('Navigating to http://localhost/team...');
    await page.goto('http://localhost/team', { waitUntil: 'networkidle', timeout: 10000 });

    await page.screenshot({ path: 'team-page-error.png', fullPage: true });
    console.log('Screenshot saved to team-page-error.png');

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
```

Run with:
```bash
node test-team-error.js
```

### 2. Test Records Page with Login (`test-records-logged-in.js`)

Tests the records page after authentication:

```javascript
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  page.on('console', msg => {
    console.log('Browser console:', msg.text());
  });

  try {
    // Login
    console.log('Logging in...');
    await page.goto('http://localhost/login', { waitUntil: 'networkidle', timeout: 10000 });
    await page.fill('input[name="login"]', 'test2@topseven.fr');
    await page.fill('input[name="password"]', 'Passw0rd');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);

    // Test records page
    console.log('Navigating to http://localhost/records...');
    await page.goto('http://localhost/records', { waitUntil: 'networkidle', timeout: 10000 });

    await page.screenshot({ path: 'records-page-logged-in.png', fullPage: true });
    console.log('Screenshot saved to records-page-logged-in.png');

    const bodyText = await page.locator('body').textContent();
    if (bodyText.includes('TOP7 - Error')) {
      console.log('ERROR: The page shows an error message');
    } else {
      console.log('SUCCESS: No error message found');
    }

    await page.waitForTimeout(2000);
  } catch (error) {
    console.error('Error:', error.message);
  }

  await browser.close();
})();
```

Run with:
```bash
node test-records-logged-in.js
```

### 3. Test Team Agenda (`test-agenda.js`)

Comprehensive test for the team agenda feature including:
- Event listing and display
- Event details viewing
- Availability management (Available/Maybe/Unavailable)
- Event creation with form validation
- Month navigation

**Test Data Available:**
The database includes sample events:
- "Match amical contre les Tigres" - Match amical (Nov 25, 2025)
- "Visionnage match France-NZ" - Visionnage (Nov 22, 2025) - Confirmed
- "Réunion tactique" - Réunion (Nov 28, 2025)
- "Entraînement collectif" - Autre (Dec 5, 2025)

```javascript
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const page = await browser.newPage();

  // Login
  await page.goto('http://localhost/login');
  await page.fill('input[name="login"]', 'test2@topseven.fr');
  await page.fill('input[name="password"]', 'Passw0rd');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(2000);

  // Navigate to agenda
  await page.goto('http://localhost/agenda');
  await page.screenshot({ path: 'agenda-main.png', fullPage: true });

  // Click on first event
  await page.locator('.bg-white.rounded-lg.shadow').first().click();
  await page.waitForTimeout(1000);
  await page.screenshot({ path: 'agenda-event-details.png' });

  // Set availability to "Available"
  await page.locator('button:has-text("Disponible")').click();
  await page.waitForTimeout(1500);
  await page.screenshot({ path: 'agenda-availability-set.png' });

  // Close modal
  await page.locator('#event-details-modal button:has-text("×")').click();

  // Test create event
  await page.locator('button:has-text("Nouvel Événement")').click();
  await page.waitForTimeout(1000);

  // Fill form
  await page.fill('input[name="title"]', 'Test Event');
  await page.fill('input[name="date"]', '2025-11-30');
  await page.fill('input[name="time"]', '18:00');
  await page.fill('input[name="location"]', 'Test Location');

  await page.screenshot({ path: 'agenda-create-form.png' });

  // Submit
  await page.locator('#create-event-form button[type="submit"]').click();
  await page.waitForTimeout(2000);

  await browser.close();
})();
```

Run with:
```bash
node test-agenda.js
```

**What it tests:**
- Page loads without errors
- Events are displayed correctly
- Event details modal opens
- Availability buttons work
- Event creation form submits
- Month navigation works
- Takes 9 screenshots documenting the full workflow

## Debugging Tips

### 1. Run in Non-Headless Mode

See the browser actions in real-time:

```javascript
const browser = await chromium.launch({ headless: false });
```

### 2. Slow Down Actions

Add delays to observe what's happening:

```javascript
const browser = await chromium.launch({
  headless: false,
  slowMo: 500  // 500ms delay between actions
});
```

### 3. Pause Execution

Use `page.pause()` to debug interactively:

```javascript
await page.pause();  // Opens Playwright Inspector
```

### 4. Enable Verbose Logging

```javascript
const browser = await chromium.launch({
  headless: false,
  args: ['--enable-logging', '--v=1']
});
```

### 5. Check Network Activity

```javascript
page.on('request', request => {
  console.log('Request:', request.url());
});

page.on('response', response => {
  console.log('Response:', response.status(), response.url());
});
```

## Checking Docker Logs

After running tests, check application logs:

```bash
# Check PHP error logs
docker exec test-web-1 tail -50 /tmp/log_20251120.txt

# Check Apache error logs
docker logs test-web-1 --tail 50

# Check database logs
docker logs test-db-1 --tail 50
```

## Best Practices

1. **Always close the browser**: Use try/finally to ensure cleanup
   ```javascript
   try {
     // test code
   } finally {
     await browser.close();
   }
   ```

2. **Use appropriate waits**: Don't rely on fixed timeouts
   ```javascript
   // Good
   await page.waitForSelector('.results');

   // Avoid when possible
   await page.waitForTimeout(5000);
   ```

3. **Handle errors gracefully**: Wrap navigation in try/catch
   ```javascript
   try {
     await page.goto(url);
   } catch (error) {
     console.error('Navigation failed:', error.message);
   }
   ```

4. **Take screenshots on failure**: Helps diagnose issues
   ```javascript
   } catch (error) {
     await page.screenshot({ path: 'error-state.png' });
     throw error;
   }
   ```

5. **Use meaningful file names**: Include timestamps or test names
   ```javascript
   const timestamp = new Date().toISOString().replace(/:/g, '-');
   await page.screenshot({ path: `test-${timestamp}.png` });
   ```

## Useful Selectors

```javascript
// By ID
await page.locator('#palmares')

// By class
await page.locator('.player-stats')

// By attribute
await page.locator('input[name="login"]')

// By text content
await page.locator('text=Records')

// By role
await page.locator('role=button[name="Submit"]')

// CSS selector
await page.locator('table.player tr')

// XPath
await page.locator('xpath=//button[@type="submit"]')
```

## Integration with CI/CD

For automated testing in CI/CD pipelines:

```javascript
const browser = await chromium.launch({
  headless: true,  // No GUI in CI
  args: [
    '--no-sandbox',
    '--disable-dev-shm-usage'
  ]
});

// Exit with error code on failure
process.exitCode = testsPassed ? 0 : 1;
```

## Additional Resources

- [Playwright Documentation](https://playwright.dev/docs/intro)
- [Playwright API Reference](https://playwright.dev/docs/api/class-playwright)
- [Selectors Guide](https://playwright.dev/docs/selectors)
- [Best Practices](https://playwright.dev/docs/best-practices)

## Troubleshooting

### Browser doesn't launch

```bash
# Reinstall Chromium
npx playwright install chromium --force
```

### Timeout errors

Increase timeout or wait for specific conditions:

```javascript
await page.goto(url, {
  waitUntil: 'networkidle',
  timeout: 30000  // 30 seconds
});
```

### Connection refused

Ensure Docker containers are running:

```bash
docker ps
```

Check application is accessible:

```bash
curl http://localhost
```

## Quick Reference: All Test Commands

### Install Playwright
```bash
npx -y playwright install chromium
```

### Run Comprehensive Test Suite
```bash
# Test all pages and generate HTML report
node test-all-pages.js

# View the report
start test-screenshots/report.html  # Windows
xdg-open test-screenshots/report.html  # Linux
open test-screenshots/report.html  # Mac
```

### Run Individual Tests
```bash
# Test team page
node test-team-error.js

# Test records page with login
node test-records-logged-in.js
```

### Check Docker Logs After Testing
```bash
# View application logs
docker exec test-web-1 tail -100 /tmp/log_$(date +%Y%m%d).txt

# View Apache error logs
docker logs test-web-1 --tail 50

# View database logs
docker logs test-db-1 --tail 50
```

### Clean Up Test Artifacts
```bash
# Remove screenshots
rm -rf test-screenshots/*.png

# Remove all test artifacts
rm -rf test-screenshots/
```

## Test Organization

```
top7gbo2/
├── test-all-pages.js              # Comprehensive test suite
├── test-team-error.js              # Team page test
├── test-records-logged-in.js       # Records page test
├── test-screenshots/               # Generated screenshots
│   ├── report.html                 # HTML test report
│   ├── _index_public.png          # Screenshots...
│   └── ...
└── PLAYWRIGHT_TESTING.md          # This guide
```
