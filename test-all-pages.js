const { chromium } = require('playwright');
const fs = require('fs');

// Configuration
const BASE_URL = 'http://localhost';
const TEST_USER = {
  login: 'test2@topseven.fr',
  password: 'Passw0rd'
};

// Pages to test
const PAGES = {
  public: [
    { path: '/', name: 'Home (index)' },
    { path: '/login', name: 'Login' },
    { path: '/register', name: 'Register' },
    { path: '/password', name: 'Password Reset' },
    { path: '/intro', name: 'Introduction' },
  ],
  authenticated: [
    { path: '/player', name: 'Player Profile' },
    { path: '/team', name: 'Team Management' },
    { path: '/prono', name: 'Pronostics' },
    { path: '/rank', name: 'Rankings' },
    { path: '/rank7', name: 'Rank 7' },
    { path: '/records', name: 'Records' },
    { path: '/stats', name: 'Statistics' },
    { path: '/stats_graphs', name: 'Statistics Graphs' },
    { path: '/calendar', name: 'Calendar' },
    { path: '/lnr', name: 'LNR Rankings' },
    { path: '/info', name: 'Information' },
    { path: '/params', name: 'Parameters' },
    { path: '/agenda', name: 'Team Agenda' },
    { path: '/register_new_season', name: 'Register New Season' },
  ],
  display: [
    { path: '/display', name: 'Display (General)' },
  ]
};

// Test results
const results = {
  passed: [],
  failed: [],
  warnings: [],
  total: 0,
  startTime: new Date()
};

/**
 * Check page for errors
 */
function checkPageForErrors(bodyText, url) {
  const errors = [];

  if (bodyText.includes('TOP7 - Error')) {
    errors.push('Application error detected (TOP7 - Error)');
  }

  if (bodyText.includes('Warning:')) {
    errors.push('PHP Warning detected');
  }

  if (bodyText.includes('Fatal error:')) {
    errors.push('PHP Fatal Error detected');
  }

  if (bodyText.includes('Notice:')) {
    errors.push('PHP Notice detected');
  }

  if (bodyText.includes('SQLSTATE')) {
    errors.push('SQL Error detected');
  }

  if (bodyText.includes('Undefined variable')) {
    errors.push('Undefined variable error');
  }

  if (bodyText.includes('Call to undefined function')) {
    errors.push('Undefined function call');
  }

  return errors;
}

/**
 * Test a single page
 */
async function testPage(page, pageInfo, isAuthenticated = false) {
  const url = `${BASE_URL}${pageInfo.path}`;
  const testResult = {
    name: pageInfo.name,
    path: pageInfo.path,
    url: url,
    authenticated: isAuthenticated,
    status: 'unknown',
    errors: [],
    httpStatus: null,
    loadTime: 0,
    screenshot: null
  };

  console.log(`Testing: ${pageInfo.name} (${url})`);

  try {
    const startTime = Date.now();

    // Navigate to page
    const response = await page.goto(url, {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    testResult.httpStatus = response.status();
    testResult.loadTime = Date.now() - startTime;

    // Wait a bit for any dynamic content
    await page.waitForTimeout(1000);

    // Take screenshot
    const screenshotName = `test-screenshots/${pageInfo.path.replace(/\//g, '_')}_${isAuthenticated ? 'auth' : 'public'}.png`;
    await page.screenshot({
      path: screenshotName,
      fullPage: true
    });
    testResult.screenshot = screenshotName;

    // Get page content
    const bodyText = await page.locator('body').textContent();

    // Check for errors
    testResult.errors = checkPageForErrors(bodyText, url);

    // Determine status
    if (testResult.errors.length > 0) {
      testResult.status = 'failed';
      results.failed.push(testResult);
    } else if (testResult.httpStatus >= 400) {
      testResult.status = 'warning';
      testResult.errors.push(`HTTP ${testResult.httpStatus}`);
      results.warnings.push(testResult);
    } else {
      testResult.status = 'passed';
      results.passed.push(testResult);
    }

    console.log(`  ✓ Status: ${testResult.status} (${testResult.httpStatus}) - ${testResult.loadTime}ms`);
    if (testResult.errors.length > 0) {
      console.log(`  ⚠ Errors: ${testResult.errors.join(', ')}`);
    }

  } catch (error) {
    testResult.status = 'failed';
    testResult.errors.push(`Exception: ${error.message}`);
    results.failed.push(testResult);
    console.log(`  ✗ Failed: ${error.message}`);
  }

  results.total++;
  return testResult;
}

/**
 * Login to the application
 */
async function login(page) {
  console.log('\n=== Logging in ===');
  try {
    await page.goto(`${BASE_URL}/login`, {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    await page.fill('input[name="login"]', TEST_USER.login);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.click('button[type="submit"]');

    // Wait for redirect
    await page.waitForTimeout(2000);

    // Check if login was successful
    const url = page.url();
    if (url.includes('login')) {
      throw new Error('Login failed - still on login page');
    }

    console.log('✓ Login successful');
    return true;
  } catch (error) {
    console.log('✗ Login failed:', error.message);
    return false;
  }
}

/**
 * Generate HTML report
 */
function generateReport() {
  const endTime = new Date();
  const duration = (endTime - results.startTime) / 1000;

  const html = `<!DOCTYPE html>
<html>
<head>
  <title>TOP7 Test Report</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background: #f5f5f5;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    h1 {
      color: #333;
      border-bottom: 3px solid #007bff;
      padding-bottom: 10px;
    }
    .summary {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      margin: 20px 0;
    }
    .summary-card {
      padding: 20px;
      border-radius: 6px;
      text-align: center;
    }
    .summary-card h3 {
      margin: 0;
      font-size: 32px;
    }
    .summary-card p {
      margin: 5px 0 0 0;
      color: #666;
    }
    .total { background: #e3f2fd; color: #1976d2; }
    .passed { background: #e8f5e9; color: #388e3c; }
    .warnings { background: #fff3e0; color: #f57c00; }
    .failed { background: #ffebee; color: #d32f2f; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #f8f9fa;
      font-weight: 600;
      color: #333;
    }
    tr:hover {
      background: #f8f9fa;
    }
    .status-badge {
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }
    .status-passed {
      background: #4caf50;
      color: white;
    }
    .status-failed {
      background: #f44336;
      color: white;
    }
    .status-warning {
      background: #ff9800;
      color: white;
    }
    .errors {
      color: #d32f2f;
      font-size: 12px;
    }
    .screenshot-link {
      color: #007bff;
      text-decoration: none;
    }
    .screenshot-link:hover {
      text-decoration: underline;
    }
    .section {
      margin: 30px 0;
    }
    .timestamp {
      color: #666;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>TOP7 Application Test Report</h1>
    <p class="timestamp">Generated: ${endTime.toLocaleString()} | Duration: ${duration.toFixed(2)}s</p>

    <div class="summary">
      <div class="summary-card total">
        <h3>${results.total}</h3>
        <p>Total Tests</p>
      </div>
      <div class="summary-card passed">
        <h3>${results.passed.length}</h3>
        <p>Passed</p>
      </div>
      <div class="summary-card warnings">
        <h3>${results.warnings.length}</h3>
        <p>Warnings</p>
      </div>
      <div class="summary-card failed">
        <h3>${results.failed.length}</h3>
        <p>Failed</p>
      </div>
    </div>

    ${results.failed.length > 0 ? `
    <div class="section">
      <h2 style="color: #d32f2f;">❌ Failed Tests (${results.failed.length})</h2>
      <table>
        <thead>
          <tr>
            <th>Page</th>
            <th>Path</th>
            <th>HTTP Status</th>
            <th>Errors</th>
            <th>Screenshot</th>
          </tr>
        </thead>
        <tbody>
          ${results.failed.map(test => `
            <tr>
              <td><strong>${test.name}</strong></td>
              <td><code>${test.path}</code></td>
              <td>${test.httpStatus || 'N/A'}</td>
              <td class="errors">${test.errors.join('<br>')}</td>
              <td><a href="${test.screenshot}" class="screenshot-link" target="_blank">View</a></td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
    ` : ''}

    ${results.warnings.length > 0 ? `
    <div class="section">
      <h2 style="color: #f57c00;">⚠️ Warnings (${results.warnings.length})</h2>
      <table>
        <thead>
          <tr>
            <th>Page</th>
            <th>Path</th>
            <th>HTTP Status</th>
            <th>Issues</th>
            <th>Screenshot</th>
          </tr>
        </thead>
        <tbody>
          ${results.warnings.map(test => `
            <tr>
              <td><strong>${test.name}</strong></td>
              <td><code>${test.path}</code></td>
              <td>${test.httpStatus || 'N/A'}</td>
              <td class="errors">${test.errors.join('<br>')}</td>
              <td><a href="${test.screenshot}" class="screenshot-link" target="_blank">View</a></td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
    ` : ''}

    <div class="section">
      <h2 style="color: #388e3c;">✅ All Test Results</h2>
      <table>
        <thead>
          <tr>
            <th>Status</th>
            <th>Page</th>
            <th>Path</th>
            <th>Auth</th>
            <th>HTTP</th>
            <th>Load Time</th>
            <th>Screenshot</th>
          </tr>
        </thead>
        <tbody>
          ${[...results.passed, ...results.warnings, ...results.failed]
            .sort((a, b) => a.path.localeCompare(b.path))
            .map(test => `
            <tr>
              <td><span class="status-badge status-${test.status}">${test.status.toUpperCase()}</span></td>
              <td><strong>${test.name}</strong></td>
              <td><code>${test.path}</code></td>
              <td>${test.authenticated ? '🔒' : '🌐'}</td>
              <td>${test.httpStatus || 'N/A'}</td>
              <td>${test.loadTime}ms</td>
              <td><a href="${test.screenshot}" class="screenshot-link" target="_blank">View</a></td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>`;

  fs.writeFileSync('test-screenshots/report.html', html);
  console.log('\n✓ HTML report generated: test-screenshots/report.html');
}

/**
 * Main test execution
 */
async function runTests() {
  console.log('========================================');
  console.log('TOP7 Application Test Suite');
  console.log('========================================\n');

  // Create screenshots directory
  if (!fs.existsSync('test-screenshots')) {
    fs.mkdirSync('test-screenshots');
  }

  const browser = await chromium.launch({
    headless: false,
    slowMo: 100  // Slow down to see what's happening
  });

  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });

  const page = await context.newPage();

  // Listen for console messages
  page.on('console', msg => {
    if (msg.type() === 'error') {
      console.log('  Browser Error:', msg.text());
    }
  });

  try {
    // Test public pages
    console.log('\n=== Testing Public Pages ===');
    for (const pageInfo of PAGES.public) {
      await testPage(page, pageInfo, false);
      await page.waitForTimeout(500);
    }

    // Login
    const loginSuccess = await login(page);

    if (loginSuccess) {
      // Test authenticated pages
      console.log('\n=== Testing Authenticated Pages ===');
      for (const pageInfo of PAGES.authenticated) {
        await testPage(page, pageInfo, true);
        await page.waitForTimeout(500);
      }

      // Test display pages
      console.log('\n=== Testing Display Pages ===');
      for (const pageInfo of PAGES.display) {
        await testPage(page, pageInfo, true);
        await page.waitForTimeout(500);
      }
    } else {
      console.log('\n⚠️ Skipping authenticated pages due to login failure');
    }

  } catch (error) {
    console.error('Test suite error:', error);
  } finally {
    await browser.close();
  }

  // Print summary
  console.log('\n========================================');
  console.log('Test Summary');
  console.log('========================================');
  console.log(`Total:    ${results.total}`);
  console.log(`Passed:   ${results.passed.length} ✓`);
  console.log(`Warnings: ${results.warnings.length} ⚠`);
  console.log(`Failed:   ${results.failed.length} ✗`);
  console.log('========================================\n');

  // Generate report
  generateReport();

  // Exit with appropriate code
  process.exitCode = results.failed.length > 0 ? 1 : 0;
}

// Run the tests
runTests().catch(console.error);
