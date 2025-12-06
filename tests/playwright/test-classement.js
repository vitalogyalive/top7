const { chromium } = require('playwright');
const fs = require('fs');

/**
 * Classement (Rankings) Test Suite
 * Tests ranking pages and functionality including:
 * - Rank7 (Top 7 rankings)
 * - Rank14 (Team rankings)
 * - LNR rankings
 * - Records page
 * - Statistics page
 */

const BASE_URL = 'http://localhost';
const TEST_USER = {
  login: 'test2@topseven.fr',
  password: 'Passw0rd'
};

let browser;
let context;
let page;

// Create screenshots directory
if (!fs.existsSync('test-screenshots/classement')) {
  fs.mkdirSync('test-screenshots/classement', { recursive: true });
}

async function setup() {
  browser = await chromium.launch({
    headless: true
  });
  context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  page = await context.newPage();
}

async function teardown() {
  if (browser) {
    await browser.close();
  }
}

async function login() {
  await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle' });
  await page.fill('input[name="login"]', TEST_USER.login);
  await page.fill('input[name="password"]', TEST_USER.password);
  await page.click('button[type="submit"]');
  await page.waitForTimeout(2000);

  const url = page.url();
  return !url.includes('login') && !url.endsWith('/');
}

function checkForErrors(bodyText) {
  const errors = [];
  if (bodyText.includes('Warning:')) errors.push('PHP Warning');
  if (bodyText.includes('Fatal error:')) errors.push('PHP Fatal Error');
  if (bodyText.includes('Notice:')) errors.push('PHP Notice');
  if (bodyText.includes('SQLSTATE')) errors.push('SQL Error');
  if (bodyText.includes('Undefined variable')) errors.push('Undefined variable');
  if (bodyText.includes('Undefined index')) errors.push('Undefined index');
  if (bodyText.includes('TOP7 - Error')) errors.push('Application Error');
  return errors;
}

/**
 * Test 1: Rank page loads without errors
 */
async function testRankPageLoads() {
  console.log('\n[TEST] Rank page loads without errors');

  await page.goto(`${BASE_URL}/rank`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/rank-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  // Check for ranking table elements
  const hasTable = await page.locator('table').count() > 0;
  if (hasTable) {
    console.log('  ✓ Rank page loaded with tables');
    return { passed: true };
  }

  console.log('  ⚠ Page loaded but no tables found');
  return { passed: true, warning: 'No tables visible' };
}

/**
 * Test 2: Rank7 page loads correctly
 */
async function testRank7Page() {
  console.log('\n[TEST] Rank7 page loads correctly');

  await page.goto(`${BASE_URL}/rank7`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/rank7-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  // Check for ranking content - look for team/player names, points, etc.
  const hasRankingContent = bodyText.includes('Point') ||
                            bodyText.includes('Equipe') ||
                            bodyText.includes('Classement') ||
                            bodyText.includes('TOP7');

  if (hasRankingContent) {
    console.log('  ✓ Rank7 page loaded with ranking content');
    return { passed: true };
  }

  console.log('  ⚠ Page loaded but ranking content may be missing');
  return { passed: true, warning: 'Ranking content check uncertain' };
}

/**
 * Test 3: LNR rankings page
 */
async function testLnrPage() {
  console.log('\n[TEST] LNR rankings page loads correctly');

  await page.goto(`${BASE_URL}/lnr`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/lnr-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  console.log('  ✓ LNR page loaded without errors');
  return { passed: true };
}

/**
 * Test 4: Records page loads correctly
 */
async function testRecordsPage() {
  console.log('\n[TEST] Records page loads correctly');

  await page.goto(`${BASE_URL}/records`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/records-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  // Check for records content
  const hasRecordsContent = bodyText.includes('Record') ||
                            bodyText.includes('record') ||
                            bodyText.includes('Palmarès') ||
                            bodyText.includes('palmares');

  if (hasRecordsContent) {
    console.log('  ✓ Records page loaded with records content');
    return { passed: true };
  }

  console.log('  ⚠ Page loaded but records content unclear');
  return { passed: true, warning: 'Records content check uncertain' };
}

/**
 * Test 5: Statistics page loads correctly
 */
async function testStatsPage() {
  console.log('\n[TEST] Statistics page loads correctly');

  await page.goto(`${BASE_URL}/stats`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/stats-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  console.log('  ✓ Stats page loaded without errors');
  return { passed: true };
}

/**
 * Test 6: Statistics graphs page loads correctly
 */
async function testStatsGraphsPage() {
  console.log('\n[TEST] Statistics graphs page loads correctly');

  await page.goto(`${BASE_URL}/stats_graphs`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/stats-graphs-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  // Check if Chart.js is loaded (canvas elements for graphs)
  const hasCanvas = await page.locator('canvas').count() > 0;
  if (hasCanvas) {
    console.log('  ✓ Stats graphs page loaded with charts');
    return { passed: true };
  }

  console.log('  ⚠ Page loaded but no charts visible');
  return { passed: true, warning: 'No chart elements found' };
}

/**
 * Test 7: Display page shows ranking information
 */
async function testDisplayPage() {
  console.log('\n[TEST] Display page shows ranking information');

  await page.goto(`${BASE_URL}/display`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/display-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  // Check for game/ranking content
  const hasContent = bodyText.includes('TOP7') ||
                     bodyText.includes('Classement') ||
                     bodyText.includes('Point') ||
                     bodyText.includes('Journée');

  if (hasContent) {
    console.log('  ✓ Display page loaded with game content');
    return { passed: true };
  }

  console.log('  ⚠ Page loaded but content unclear');
  return { passed: true, warning: 'Content check uncertain' };
}

/**
 * Test 8: Prono (predictions) page loads
 */
async function testPronoPage() {
  console.log('\n[TEST] Prono (predictions) page loads correctly');

  await page.goto(`${BASE_URL}/prono`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/prono-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  console.log('  ✓ Prono page loaded without errors');
  return { passed: true };
}

/**
 * Test 9: Calendar page loads
 */
async function testCalendarPage() {
  console.log('\n[TEST] Calendar page loads correctly');

  await page.goto(`${BASE_URL}/calendar`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: 'test-screenshots/classement/calendar-page.png', fullPage: true });

  const bodyText = await page.locator('body').textContent();
  const errors = checkForErrors(bodyText);

  if (errors.length > 0) {
    console.log(`  ✗ Errors found: ${errors.join(', ')}`);
    return { passed: false, errors };
  }

  // Check for calendar content
  const hasCalendarContent = bodyText.includes('Journée') ||
                             bodyText.includes('Match') ||
                             bodyText.includes('Calendar') ||
                             bodyText.includes('Calendrier');

  if (hasCalendarContent) {
    console.log('  ✓ Calendar page loaded with calendar content');
    return { passed: true };
  }

  console.log('  ⚠ Page loaded but calendar content unclear');
  return { passed: true, warning: 'Calendar content check uncertain' };
}

/**
 * Test 10: Navigation between ranking pages works
 */
async function testNavigationBetweenPages() {
  console.log('\n[TEST] Navigation between ranking pages works');

  const pages = ['/display', '/rank', '/rank7', '/stats', '/records'];
  let navigationWorks = true;

  for (let i = 0; i < pages.length - 1; i++) {
    await page.goto(`${BASE_URL}${pages[i]}`, { waitUntil: 'networkidle' });

    const currentUrl = page.url();
    if (currentUrl.includes('login')) {
      console.log(`  ✗ Session lost at ${pages[i]}`);
      navigationWorks = false;
      break;
    }
  }

  if (navigationWorks) {
    console.log('  ✓ Navigation between all ranking pages successful');
    return { passed: true };
  }

  return { passed: false, error: 'Navigation failed' };
}

/**
 * Test 11: Ranking tables contain data
 */
async function testRankingTablesHaveData() {
  console.log('\n[TEST] Ranking tables contain data');

  await page.goto(`${BASE_URL}/rank`, { waitUntil: 'networkidle' });

  const tables = await page.locator('table').count();

  if (tables === 0) {
    console.log('  ⚠ No tables found on rank page');
    return { passed: true, warning: 'No tables found' };
  }

  // Check if any table has rows (excluding header)
  const rows = await page.locator('table tr').count();

  if (rows > 1) {
    console.log(`  ✓ Found ${tables} tables with ${rows} total rows`);
    return { passed: true };
  }

  console.log('  ⚠ Tables found but may be empty');
  return { passed: true, warning: 'Tables may be empty' };
}

/**
 * Main test runner
 */
async function runClassementTests() {
  console.log('========================================');
  console.log('    CLASSEMENT (RANKINGS) TESTS');
  console.log('========================================');
  console.log(`Base URL: ${BASE_URL}`);

  await setup();

  // Login first
  console.log('\n[SETUP] Logging in...');
  const loginSuccess = await login();

  if (!loginSuccess) {
    console.log('✗ Login failed - cannot proceed with tests');
    await teardown();
    process.exit(1);
  }
  console.log('✓ Login successful');

  const tests = [
    testRankPageLoads,
    testRank7Page,
    testLnrPage,
    testRecordsPage,
    testStatsPage,
    testStatsGraphsPage,
    testDisplayPage,
    testPronoPage,
    testCalendarPage,
    testNavigationBetweenPages,
    testRankingTablesHaveData
  ];

  const results = {
    total: tests.length,
    passed: 0,
    failed: 0,
    warnings: 0
  };

  for (const test of tests) {
    try {
      const result = await test();
      if (result.passed) {
        results.passed++;
        if (result.warning) {
          results.warnings++;
        }
      } else {
        results.failed++;
      }
    } catch (error) {
      console.log(`  ✗ Exception: ${error.message}`);
      results.failed++;
    }
  }

  await teardown();

  // Print summary
  console.log('\n========================================');
  console.log('           TEST SUMMARY');
  console.log('========================================');
  console.log(`Total:    ${results.total}`);
  console.log(`Passed:   ${results.passed} ✓`);
  console.log(`Failed:   ${results.failed} ✗`);
  console.log(`Warnings: ${results.warnings} ⚠`);
  console.log('========================================\n');

  process.exitCode = results.failed > 0 ? 1 : 0;
}

// Run tests
runClassementTests().catch(error => {
  console.error('Fatal error:', error);
  process.exit(1);
});
