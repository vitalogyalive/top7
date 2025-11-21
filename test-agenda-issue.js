const playwright = require('playwright');

(async () => {
    const browser = await playwright.chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();

    // Capture console logs and errors
    page.on('console', msg => console.log('CONSOLE:', msg.text()));
    page.on('pageerror', error => console.log('JS ERROR:', error.message));

    // Capture network requests
    page.on('requestfailed', request => {
        console.log('REQUEST FAILED:', request.url(), request.failure().errorText);
    });

    try {
        console.log('1. Loading login page...');
        await page.goto('http://localhost/index.php');
        await page.waitForLoadState('networkidle');

        console.log('2. Filling login form...');
        await page.fill('input[name="login"]', 'test1@topseven.fr');
        await page.fill('input[name="password"]', 'test123');

        console.log('3. Submitting login...');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);

        const currentUrl = page.url();
        console.log('4. After login URL:', currentUrl);

        console.log('5. Navigating to agenda page...');
        await page.goto('http://localhost/agenda.php');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);

        console.log('6. Checking page state...');
        const title = await page.textContent('h1').catch(() => 'Not found');
        console.log('   Page title:', title);

        const eventsListHTML = await page.innerHTML('#events-list').catch(() => 'Not found');
        console.log('   Events list HTML length:', eventsListHTML.length);
        console.log('   Events list content:', eventsListHTML.substring(0, 200));

        const noEventsVisible = await page.isVisible('#no-events');
        console.log('   No events message visible:', noEventsVisible);

        console.log('7. Taking screenshot...');
        await page.screenshot({ path: 'agenda-issue.png', fullPage: true });

    } catch (error) {
        console.error('ERROR:', error.message);
    } finally {
        await browser.close();
    }
})();
