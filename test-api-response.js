const playwright = require('playwright');

(async () => {
    const browser = await playwright.chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();

    // Capture API responses
    page.on('response', async response => {
        if (response.url().includes('agenda_api.php')) {
            console.log('\n=== API RESPONSE ===');
            console.log('URL:', response.url());
            console.log('Status:', response.status());
            const body = await response.text();
            console.log('Body (first 500 chars):');
            console.log(body.substring(0, 500));
            console.log('===================\n');
        }
    });

    try {
        // Login
        console.log('Logging in...');
        await page.goto('http://localhost/index.php');
        await page.fill('input[name="login"]', 'test1@topseven.fr');
        await page.fill('input[name="password"]', 'test123');
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        // Navigate to agenda to trigger API call
        console.log('Loading agenda page...');
        await page.goto('http://localhost/agenda.php');
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);

    } catch (error) {
        console.error('Error:', error.message);
    } finally {
        await browser.close();
    }
})();
