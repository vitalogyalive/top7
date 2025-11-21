# TOP7 Testing Suite

This directory contains automated tests for the TOP7 application using Playwright.

## Quick Start

```bash
# 1. Install Playwright (one-time setup)
npx -y playwright install chromium

# 2. Run comprehensive test suite
node test-all-pages.js

# 3. View the HTML report
start test-screenshots/report.html
```

## Available Tests

### 🚀 Comprehensive Test Suite (`test-all-pages.js`)

**The main test suite** - Tests all pages in the application and generates a beautiful HTML report.

- **Tests 19+ pages** (public and authenticated)
- **Automatic error detection** (PHP warnings, SQL errors, etc.)
- **Screenshots** of every page
- **Load time measurements**
- **HTML report** with color-coded results

**Run it:**
```bash
node test-all-pages.js
```

**Output:**
- Console: Real-time test progress
- `test-screenshots/` folder: All screenshots
- `test-screenshots/report.html`: Detailed HTML report

### 🔍 Individual Page Tests

#### Test Team Page (`test-team-error.js`)
Tests the team management page for PHP warnings and errors.

```bash
node test-team-error.js
```

#### Test Records Page (`test-records-logged-in.js`)
Tests the records page with authentication.

```bash
node test-records-logged-in.js
```

#### Test Team Agenda (`test-agenda.js`)
Comprehensive test of the team agenda feature with event management and availability tracking.

```bash
node test-agenda.js
```

**Features tested:**
- Event listing and calendar display
- Event details modal
- Availability management (✅ Available, ⚠️ Maybe, ❌ Unavailable)
- Event creation with full form
- Month navigation
- Takes 9 screenshots of the complete workflow

## What Gets Tested

### Error Detection
The tests automatically check for:
- ✓ PHP Warnings
- ✓ PHP Fatal Errors
- ✓ PHP Notices
- ✓ SQL Errors (SQLSTATE)
- ✓ Application Errors (TOP7 - Error)
- ✓ Undefined Variables
- ✓ Undefined Functions
- ✓ HTTP Error Status Codes

### Pages Tested

**Public Pages:**
- Home / Index
- Login
- Register
- Password Reset
- Introduction

**Authenticated Pages:**
- Player Profile
- Team Management
- Pronostics
- Rankings & Rank7
- Records
- Statistics
- Statistics Graphs
- Calendar
- LNR Rankings
- Information
- Parameters
- Team Agenda
- New Season Registration
- Display Pages

## Understanding Test Results

### Status Indicators

- **✓ PASSED** (Green) - Page loaded successfully with no errors
- **⚠ WARNING** (Orange) - Page loaded but has issues (e.g., HTTP 404)
- **✗ FAILED** (Red) - Page has PHP errors, SQL errors, or failed to load

### HTML Report Features

The generated report includes:

1. **Summary Cards**
   - Total tests run
   - Passed count
   - Warnings count
   - Failed count

2. **Failed Tests Section**
   - Detailed error messages
   - HTTP status codes
   - Links to screenshots

3. **Complete Results Table**
   - All pages tested
   - Authentication status (🔒 locked / 🌐 public)
   - Load times
   - Interactive screenshots

## Configuration

### Test Credentials

Edit the test files to change login credentials:

```javascript
const TEST_USER = {
  login: 'test2@topseven.fr',
  password: 'Passw0rd'
};
```

### Add New Pages to Test

Edit `test-all-pages.js`:

```javascript
const PAGES = {
  authenticated: [
    { path: '/your-new-page', name: 'Your New Page' },
    // ... existing pages
  ]
};
```

### Adjust Timeouts

```javascript
await page.goto(url, {
  waitUntil: 'networkidle',
  timeout: 15000  // Change timeout in milliseconds
});
```

## Debugging

### Run in Visible Mode

By default, tests run with the browser visible. To run headless:

```javascript
const browser = await chromium.launch({
  headless: true  // Change to false to see browser
});
```

### Slow Down Execution

```javascript
const browser = await chromium.launch({
  headless: false,
  slowMo: 500  // 500ms delay between actions
});
```

### Check Application Logs

After running tests, check PHP logs:

```bash
# Today's log file
docker exec test-web-1 tail -100 /tmp/log_$(date +%Y%m%d).txt

# Apache errors
docker logs test-web-1 --tail 50
```

## CI/CD Integration

For automated testing in CI/CD pipelines:

```bash
# Run headless
export HEADLESS=true
node test-all-pages.js

# Check exit code
if [ $? -eq 0 ]; then
  echo "All tests passed!"
else
  echo "Tests failed!"
  exit 1
fi
```

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
```

Start containers if needed:
```bash
docker-compose up -d
```

### "Browser didn't launch"

Reinstall Chromium:
```bash
npx playwright install chromium --force
```

### Tests timeout

Increase timeout in the test file:
```javascript
timeout: 30000  // 30 seconds
```

## Best Practices

1. **Run tests regularly** - Catch issues early
2. **Check the HTML report** - Visual verification of pages
3. **Review screenshots** - Verify UI rendering
4. **Monitor load times** - Identify performance issues
5. **Update tests** - When adding new pages or features

## File Structure

```
top7gbo2/
├── test-all-pages.js              # Main comprehensive test
├── test-team-error.js              # Team page test
├── test-records-logged-in.js       # Records page test
├── test-screenshots/               # Generated by tests
│   ├── report.html                 # Test results report
│   └── *.png                       # Page screenshots
├── PLAYWRIGHT_TESTING.md          # Detailed guide
└── TEST_README.md                 # This file
```

## Additional Resources

- 📖 [Full Testing Guide](PLAYWRIGHT_TESTING.md) - Comprehensive documentation
- 🌐 [Playwright Docs](https://playwright.dev/) - Official documentation
- 🐛 [Report Issues](https://github.com/anthropics/claude-code/issues) - For testing tool issues

## Examples

### Run and View Report
```bash
# Run tests
node test-all-pages.js

# Open report (Windows)
start test-screenshots/report.html

# Open report (Linux)
xdg-open test-screenshots/report.html

# Open report (Mac)
open test-screenshots/report.html
```

### Check for Specific Errors
```bash
# Check if any tests failed
node test-all-pages.js | grep "Failed:"

# Count errors
node test-all-pages.js | grep -c "✗"
```

### Clean Up
```bash
# Remove all screenshots
rm -rf test-screenshots/*.png

# Remove entire test results directory
rm -rf test-screenshots/
```

---

**Happy Testing! 🎉**

For detailed documentation, see [PLAYWRIGHT_TESTING.md](PLAYWRIGHT_TESTING.md)
