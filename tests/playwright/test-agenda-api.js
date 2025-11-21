const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext();
  const page = await context.newPage();

  // Intercept API calls to see responses
  page.on('response', async response => {
    if (response.url().includes('agenda_api.php')) {
      console.log('\n=== API Response ===');
      console.log('URL:', response.url());
      console.log('Status:', response.status());
      try {
        const body = await response.text();
        console.log('Body:', body);
        const json = JSON.parse(body);
        console.log('Parsed:', JSON.stringify(json, null, 2));
      } catch (e) {
        console.log('Could not parse response');
      }
    }
  });

  // Capture console messages
  page.on('console', msg => {
    console.log('Browser:', msg.type(), msg.text());
  });

  // Login
  console.log('Logging in...');
  await page.goto('http://localhost/login', { waitUntil: 'networkidle' });
  await page.fill('input[name="login"]', 'test2@topseven.fr');
  await page.fill('input[name="password"]', 'Passw0rd');
  await page.click('button[type="submit"]');
  await page.waitForTimeout(2000);

  // Go to agenda
  console.log('\nNavigating to agenda...');
  await page.goto('http://localhost/agenda', { waitUntil: 'networkidle' });

  // Wait for API call
  await page.waitForTimeout(3000);

  // Take screenshot
  await page.screenshot({ path: 'test-screenshots/agenda-debug.png', fullPage: true });
  console.log('\nScreenshot saved to test-screenshots/agenda-debug.png');

  // Check page content
  const bodyText = await page.locator('body').textContent();
  console.log('\n=== Checking page content ===');
  console.log('Has "Agenda":', bodyText.includes('Agenda'));
  console.log('Has error:', bodyText.includes('Error') || bodyText.includes('Warning'));

  // Check if events container exists
  const eventsListExists = await page.locator('#events-list').count() > 0;
  const noEventsExists = await page.locator('#no-events').count() > 0;
  console.log('Events list exists:', eventsListExists);
  console.log('No events message exists:', noEventsExists);

  // Check visibility
  if (eventsListExists) {
    const eventsListVisible = await page.locator('#events-list').isVisible();
    console.log('Events list visible:', eventsListVisible);

    const eventCards = await page.locator('.bg-white.rounded-lg.shadow').count();
    console.log('Event cards found:', eventCards);
  }

  if (noEventsExists) {
    const noEventsVisible = await page.locator('#no-events').isVisible();
    console.log('No events message visible:', noEventsVisible);
  }

  // Keep browser open for inspection
  console.log('\nKeeping browser open for inspection...');
  await page.pause();

  await browser.close();
})();
