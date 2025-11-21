# Playwright Tests

This directory contains all automated browser tests for the TOP7 application using Playwright.

---

## Quick Start

```bash
# 1. Install Playwright (one-time setup)
npx -y playwright install chromium

# 2. Run comprehensive test suite
node test-all-pages.js

# 3. View the HTML report
# On Linux: xdg-open screenshots/report.html
# On Windows: start screenshots/report.html
# On Mac: open screenshots/report.html
```

---

## Test Files Overview

### Comprehensive Test Suite

#### `test-all-pages.js` ⭐ **Main Test Suite**
**Purpose:** Tests all pages in the application (19+ pages) and generates a detailed HTML report.

**What it tests:**
- All public pages (home, login, register, password reset, intro)
- All authenticated pages (after logging in)
- Automatic error detection (PHP warnings, SQL errors, application errors)
- Screenshots of every page
- Load time measurements

**Output:**
- Console: Real-time test progress
- `screenshots/` folder: All screenshots
- `screenshots/report.html`: Detailed HTML report with color-coded results

**Usage:**
```bash
node test-all-pages.js
```

---

### Authentication Tests

#### `test-login-verification.js`
**Purpose:** Verifies that the login system works correctly.

**What it tests:**
- Login form submission
- Session persistence
- Access to protected pages after login
- Session validation

**Usage:**
```bash
node test-login-verification.js
```

---

### Feature-Specific Tests

#### `test-agenda.js`
**Purpose:** Comprehensive test of the Team Agenda feature.

**What it tests:**
- Event listing and calendar display
- Event details modal
- Availability management (Available/Maybe/Unavailable)
- Event creation with form validation
- Month navigation
- Takes 9 screenshots of complete workflow

**Usage:**
```bash
node test-agenda.js
```

**Test Data Required:**
- Must have sample events in the database
- User must be part of a team
- See [Agenda Documentation](../../docs/features/AGENDA.md) for setup

#### `test-agenda-api.js`
**Purpose:** Tests the Agenda API endpoints directly.

**What it tests:**
- API response structure
- List events endpoint
- Get event details endpoint
- Authentication requirements

**Usage:**
```bash
node test-agenda-api.js
```

#### `test-agenda-direct.js`
**Purpose:** Direct navigation test to agenda page.

**What it tests:**
- Page loads without errors
- Events display correctly
- No JavaScript errors

**Usage:**
```bash
node test-agenda-direct.js
```

#### `test-agenda-issue.js`
**Purpose:** Debugging test for specific agenda issues.

**What it tests:**
- Specific edge cases
- Error scenarios
- Performance issues

**Usage:**
```bash
node test-agenda-issue.js
```

---

### Page-Specific Tests

#### `test-team-error.js`
**Purpose:** Tests the team management page for errors.

**What it tests:**
- Team page loads correctly
- No PHP warnings
- No array offset errors

**Usage:**
```bash
node test-team-error.js
```

#### `test-records-logged-in.js`
**Purpose:** Tests the records page with authentication.

**What it tests:**
- Records page loads after login
- Data displays correctly
- No SQL errors

**Usage:**
```bash
node test-records-logged-in.js
```

#### `test-records-error.js`
**Purpose:** Tests records page without authentication.

**What it tests:**
- Proper redirect to login
- Error handling
- Unauthenticated access prevention

**Usage:**
```bash
node test-records-error.js
```

---

### UI/Styling Tests

#### `test-styles.js`
**Purpose:** Tests that CSS and styles load correctly.

**What it tests:**
- Tailwind CSS is loaded
- Styles are applied correctly
- No broken stylesheets
- Responsive design elements

**Usage:**
```bash
node test-styles.js
```

---

### API Tests

#### `test-api-response.js`
**Purpose:** Tests API responses and data structures.

**What it tests:**
- API endpoint availability
- Response format (JSON)
- Error handling
- Status codes

**Usage:**
```bash
node test-api-response.js
```

---

### Development Tests

#### `test-playwright.js`
**Purpose:** Basic Playwright functionality test.

**What it tests:**
- Playwright is installed correctly
- Browser launches successfully
- Basic navigation works
- Screenshot capability

**Usage:**
```bash
node test-playwright.js
```

---

## Test Configuration

### Test Credentials

Most tests use these default credentials:

```javascript
const TEST_USER = {
  login: 'test2@topseven.fr',
  password: 'Passw0rd'
};
```

To change credentials, edit the individual test files.

### Headless Mode

By default, tests run with the browser visible (`headless: false`). To run in headless mode:

```javascript
// In any test file, change:
const browser = await chromium.launch({ headless: false });

// To:
const browser = await chromium.launch({ headless: true });
```

### Timeouts

Default timeout is 10 seconds. To adjust:

```javascript
await page.goto('http://localhost/page', {
  waitUntil: 'networkidle',
  timeout: 15000  // Change to desired milliseconds
});
```

---

## Error Detection

The comprehensive test suite (`test-all-pages.js`) automatically checks for:

- ✓ **PHP Warnings** - `Warning:` in page content
- ✓ **PHP Fatal Errors** - `Fatal error:` in page content
- ✓ **PHP Notices** - `Notice:` in page content
- ✓ **SQL Errors** - `SQLSTATE` in page content
- ✓ **Application Errors** - `TOP7 - Error` in page content
- ✓ **Undefined Variables** - `Undefined variable` in page content
- ✓ **Undefined Functions** - `Undefined function` in page content
- ✓ **HTTP Errors** - Non-200 status codes

---

## Test Output

### Screenshots

All tests save screenshots to the `screenshots/` directory:

```
screenshots/
├── report.html                 # HTML test report
├── _index_public.png          # Homepage screenshot
├── _login_public.png          # Login page screenshot
├── _records_authenticated.png # Records page screenshot
└── ... (more screenshots)
```

### HTML Report

The comprehensive test generates a beautiful HTML report with:

1. **Summary Cards**
   - Total tests run
   - Passed count (green)
   - Warnings count (orange)
   - Failed count (red)

2. **Failed Tests Section**
   - Detailed error messages
   - HTTP status codes
   - Links to screenshots

3. **Complete Results Table**
   - All pages tested
   - Authentication status (🔒 locked / 🌐 public)
   - Load times
   - Interactive screenshots

### Console Output

Real-time console output shows:
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

========================================
Test Summary
========================================
Total:    19
Passed:   18 ✓
Warnings: 0 ⚠
Failed:   1 ✗
========================================
```

---

## Debugging Tests

### Run in Visible Mode

See the browser actions in real-time:

```javascript
const browser = await chromium.launch({ headless: false });
```

### Slow Down Actions

Add delays to observe what's happening:

```javascript
const browser = await chromium.launch({
  headless: false,
  slowMo: 500  // 500ms delay between actions
});
```

### Pause Execution

Use `page.pause()` to debug interactively:

```javascript
await page.pause();  // Opens Playwright Inspector
```

### Enable Verbose Logging

```javascript
page.on('request', request => {
  console.log('Request:', request.url());
});

page.on('response', response => {
  console.log('Response:', response.status(), response.url());
});

page.on('console', msg => {
  console.log('Browser console:', msg.text());
});
```

---

## Checking Application Logs

After running tests, check PHP and Docker logs:

```bash
# Check PHP error logs
docker exec test-web-1 tail -50 /tmp/log_$(date +%Y%m%d).txt

# Check Apache error logs
docker logs test-web-1 --tail 50

# Check database logs
docker logs test-db-1 --tail 50

# Follow logs in real-time
docker exec test-web-1 tail -f /tmp/log_$(date +%Y%m%d).txt
```

---

## CI/CD Integration

For automated testing in CI/CD pipelines:

```javascript
// Use headless mode
const browser = await chromium.launch({
  headless: true,
  args: [
    '--no-sandbox',
    '--disable-dev-shm-usage'
  ]
});

// Exit with error code on failure
process.exitCode = testsPassed ? 0 : 1;
```

Example GitHub Actions workflow:

```yaml
name: Playwright Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: actions/setup-node@v2
        with:
          node-version: '18'
      - name: Install Playwright
        run: npx -y playwright install chromium
      - name: Run tests
        run: cd tests/playwright && node test-all-pages.js
      - name: Upload screenshots
        if: always()
        uses: actions/upload-artifact@v2
        with:
          name: test-screenshots
          path: tests/playwright/screenshots/
```

---

## Best Practices

### 1. Always Close the Browser
```javascript
try {
  // test code
} finally {
  await browser.close();
}
```

### 2. Use Appropriate Waits
```javascript
// Good
await page.waitForSelector('.results');

// Avoid when possible
await page.waitForTimeout(5000);
```

### 3. Handle Errors Gracefully
```javascript
try {
  await page.goto(url);
} catch (error) {
  console.error('Navigation failed:', error.message);
  await page.screenshot({ path: 'error-state.png' });
}
```

### 4. Take Screenshots on Failure
```javascript
catch (error) {
  await page.screenshot({ path: 'error-state.png', fullPage: true });
  throw error;
}
```

### 5. Use Meaningful File Names
```javascript
const timestamp = new Date().toISOString().replace(/:/g, '-');
await page.screenshot({ path: `test-${timestamp}.png` });
```

---

## Troubleshooting

### "Cannot find module 'playwright'"

Install Playwright:
```bash
npx -y playwright install chromium
```

### "Connection refused" or "net::ERR_CONNECTION_REFUSED"

Check Docker containers are running:
```bash
docker ps

# Start containers if needed
docker-compose up -d
```

### "Browser didn't launch"

Reinstall Chromium:
```bash
npx playwright install chromium --force
```

### Tests Timeout

Increase timeout in the test file:
```javascript
await page.goto(url, {
  waitUntil: 'networkidle',
  timeout: 30000  // 30 seconds
});
```

### Application Not Accessible

Verify the application is running:
```bash
curl http://localhost
```

---

## Maintenance

### Clean Up Old Screenshots

```bash
# Remove all screenshots
rm -rf screenshots/*.png

# Remove entire screenshots directory
rm -rf screenshots/
```

### Update Test Data

When the application changes:
1. Update test credentials if authentication changes
2. Update expected page structures if UI changes
3. Update API endpoint tests if API changes
4. Review and update error detection patterns

---

## Additional Resources

- **Full Testing Guide:** [../docs/testing/PLAYWRIGHT_GUIDE.md](../../docs/testing/PLAYWRIGHT_GUIDE.md)
- **Feature Documentation:** [../docs/features/](../../docs/features/)
- **Playwright Docs:** [https://playwright.dev/](https://playwright.dev/)
- **Playwright API:** [https://playwright.dev/docs/api/class-playwright](https://playwright.dev/docs/api/class-playwright)

---

## Contributing

When adding new tests:
1. Follow the naming convention: `test-<feature>.js`
2. Add documentation to this README
3. Include error handling and screenshots
4. Test both success and failure cases
5. Add to the comprehensive test suite if appropriate

---

## Summary

| Test File | Purpose | Run Time | Critical |
|-----------|---------|----------|----------|
| `test-all-pages.js` | Full application test | ~60s | ⭐⭐⭐ |
| `test-login-verification.js` | Login system | ~10s | ⭐⭐⭐ |
| `test-agenda.js` | Agenda feature | ~30s | ⭐⭐ |
| `test-records-logged-in.js` | Records page | ~15s | ⭐⭐ |
| `test-team-error.js` | Team page | ~10s | ⭐ |
| `test-styles.js` | UI/CSS | ~5s | ⭐ |

**Critical tests** should be run before every deployment.

---

**Happy Testing! 🎉**
