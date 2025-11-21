const playwright = require('playwright');

(async () => {
    const browser = await playwright.chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();

    // Enable console logging
    page.on('console', msg => console.log('BROWSER:', msg.text()));
    page.on('pageerror', error => console.log('ERROR:', error.message));

    try {
        // First login
        await page.goto('http://localhost/index.php');
        await page.fill('input[name="pseudo"]', 'paco');
        await page.fill('input[name="password"]', 'paco');
        await page.click('input[type="submit"]');
        await page.waitForLoadState('networkidle');

        // Now go to agenda
        console.log('\n=== Navigating to agenda page ===');
        await page.goto('http://localhost/agenda.php');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);

        // Check for JavaScript errors
        console.log('\n=== Checking page content ===');
        const title = await page.textContent('h1');
        console.log('Page title:', title);

        const eventsListVisible = await page.isVisible('#events-list');
        const noEventsVisible = await page.isVisible('#no-events');

        console.log('Events list visible:', eventsListVisible);
        console.log('No events message visible:', noEventsVisible);

        // Check if API call was made
        const apiCalls = [];
        page.on('response', response => {
            if (response.url().includes('agenda_api.php')) {
                console.log('API Response:', response.url(), response.status());
            }
        });

        // Take screenshot
        await page.screenshot({ path: 'agenda-debug.png', fullPage: true });
        console.log('\n=== Screenshot saved as agenda-debug.png ===');

    } catch (error) {
        console.error('Error:', error);
    } finally {
        await browser.close();
    }
})();
