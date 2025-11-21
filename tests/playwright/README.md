# Playwright Tests

This directory contains all automated browser tests for the TOP7 application using Playwright.

---

## Directory Structure

```
tests/playwright/
├── README.md                  # This file - comprehensive test documentation
├── test-*.js                  # JavaScript/Node.js Playwright tests
├── python/                    # Python Playwright tests
│   ├── test_authenticated_playwright.py
│   ├── test_menu_navigation_playwright.py
│   └── ... (more Python tests)
└── screenshots/               # Generated test screenshots and reports
```

**Companion Directories:**
- `test-data/` (in project root) - Local setup files: Docker configs, database scripts, SQL files for test environment

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

## Python Tests

The `python/` directory contains Python-based Playwright tests that provide comprehensive test coverage with detailed reporting and screenshot capabilities.

### Prerequisites

```bash
# Install Python Playwright
pip install playwright
python -m playwright install chromium
```

### Python Test Files

#### `test_authenticated_playwright.py` ⭐ **Comprehensive Authenticated Test**
**Purpose:** Complete test suite for authenticated user workflows.

**What it tests:**
- User login functionality
- Main dashboard content after authentication
- Key page navigation (Prono, Ranking, Team, Display pages)
- Session information and persistence
- Logout functionality
- Screenshots at each step

**Output:**
- `playwright_test_results/` directory with timestamped folders
- Multiple screenshots showing each test step
- JSON results file with detailed test data

**Usage:**
```bash
cd tests/playwright/python
python3 test_authenticated_playwright.py
```

#### `test_test2_login_playwright.py`
**Purpose:** Login verification for test2@topseven.fr user.

**What it tests:**
- Login form submission
- Session creation
- Post-login navigation
- User-specific content display

**Usage:**
```bash
cd tests/playwright/python
python3 test_test2_login_playwright.py
```

#### `test_menu_navigation_playwright.py`
**Purpose:** Tests complete menu navigation system.

**What it tests:**
- All menu items accessibility
- Page transitions
- Menu state after navigation
- Error-free page loads

**Usage:**
```bash
cd tests/playwright/python
python3 test_menu_navigation_playwright.py
```

#### `test_all_menus_playwright.py`
**Purpose:** Comprehensive menu testing across the application.

**What it tests:**
- All available menus
- Menu item visibility based on authentication
- Menu interaction patterns
- Navigation between menus

**Usage:**
```bash
cd tests/playwright/python
python3 test_all_menus_playwright.py
```

#### `test_display_function_playwright.py`
**Purpose:** Tests the display/visualization functions.

**What it tests:**
- Display page accessibility
- Data visualization rendering
- Chart and graph display
- Export functionality

**Usage:**
```bash
cd tests/playwright/python
python3 test_display_function_playwright.py
```

#### `test_topseven7_playwright.py`
**Purpose:** Tests TopSeven7 specific features.

**What it tests:**
- Top7 ranking system
- Score calculations
- Player statistics
- Leaderboard display

**Usage:**
```bash
cd tests/playwright/python
python3 test_topseven7_playwright.py
```

#### `test_equipe14_playwright.py` / `test_top14_menu_playwright.py`
**Purpose:** Tests Team 14 (Equipe 14) specific functionality.

**What it tests:**
- Team-specific pages
- Team roster display
- Team statistics
- Team menu navigation

**Usage:**
```bash
cd tests/playwright/python
python3 test_equipe14_playwright.py
python3 test_top14_menu_playwright.py
```

#### `test_direct_team14_access.py`
**Purpose:** Direct access test for Team 14 to verify v1/v2 routing fixes.

**What it tests:**
- Direct URL access to team pages
- Version routing (v1/v2)
- Access permissions
- Error handling

**Usage:**
```bash
cd tests/playwright/python
python3 test_direct_team14_access.py
```

#### `test_final_menu_playwright.py`
**Purpose:** Final verification of menu system integrity.

**What it tests:**
- All menu items function correctly
- No broken links
- Consistent menu behavior
- Final smoke test for navigation

**Usage:**
```bash
cd tests/playwright/python
python3 test_final_menu_playwright.py
```

### Python Test Output

All Python tests create output in the `playwright_test_results/` directory:

```
playwright_test_results/
├── test_authenticated_20231121_123456/
│   ├── 01_before_login.png
│   ├── 02_login_filled.png
│   ├── 03_after_login.png
│   ├── test_results.json
│   └── ... (more screenshots)
└── ... (more test runs)
```

### Python Test Features

- **Detailed Logging:** Console output with emoji indicators (🎯 🚀 ✓ ❌)
- **Full-Page Screenshots:** Every step captured
- **JSON Reports:** Structured test results for CI/CD integration
- **Error Handling:** Graceful failure with error screenshots
- **Timestamp Organization:** Results organized by execution time
- **Network Idle Waiting:** Ensures pages fully load before testing

### Running All Python Tests

To run all Python tests sequentially:

```bash
cd tests/playwright/python
for test in test_*.py; do
  echo "Running $test..."
  python3 "$test"
  echo "---"
done
```

### Python vs JavaScript Tests

| Feature | Python Tests | JavaScript Tests |
|---------|--------------|------------------|
| Language | Python 3 | Node.js |
| Output | JSON + Screenshots | HTML Report + Screenshots |
| Verbosity | High (detailed console) | Medium (summary focus) |
| Best For | Individual feature testing | Comprehensive suite testing |
| Screenshots | Every step | Key pages |
| Reports | JSON structured | Interactive HTML |

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
