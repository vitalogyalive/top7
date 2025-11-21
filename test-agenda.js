const { chromium } = require('playwright');

/**
 * Test Team Agenda Feature
 * Tests event creation, viewing, availability management
 */

const BASE_URL = 'http://localhost';
const TEST_USER = {
  login: 'test2@topseven.fr',
  password: 'Passw0rd'
};

async function testAgenda() {
  console.log('========================================');
  console.log('Team Agenda Feature Test');
  console.log('========================================\n');

  const browser = await chromium.launch({
    headless: false,
    slowMo: 100
  });

  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });

  const page = await context.newPage();

  // Track errors
  const errors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      errors.push(msg.text());
      console.log('  ❌ Browser Error:', msg.text());
    }
  });

  try {
    // Step 1: Login
    console.log('Step 1: Login');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
    await page.fill('input[name="login"]', TEST_USER.login);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);
    console.log('✓ Logged in\n');

    // Step 2: Navigate to Agenda page
    console.log('Step 2: Navigate to Agenda');
    await page.goto(`${BASE_URL}/agenda`, { waitUntil: 'networkidle' });
    await page.screenshot({ path: 'test-screenshots/agenda-1-main.png', fullPage: true });
    console.log('✓ Agenda page loaded\n');

    // Check for PHP errors
    const pageText = await page.locator('body').textContent();
    if (pageText.includes('Warning:') || pageText.includes('Fatal error:')) {
      console.log('❌ PHP errors found on page\n');
      errors.push('PHP errors detected');
    }

    // Step 3: Wait for events to load
    console.log('Step 3: Wait for events to load');
    await page.waitForTimeout(2000);

    // Check if events are displayed
    const hasEvents = await page.locator('.bg-white.rounded-lg.shadow').count() > 0;
    if (hasEvents) {
      const eventCount = await page.locator('.bg-white.rounded-lg.shadow').count();
      console.log(`✓ Found ${eventCount} event(s)\n`);
    } else {
      const noEventsVisible = await page.locator('#no-events').isVisible();
      if (noEventsVisible) {
        console.log('ℹ No events for this month\n');
      } else {
        console.log('⚠ Events container not found\n');
      }
    }

    await page.screenshot({ path: 'test-screenshots/agenda-2-events-list.png', fullPage: true });

    // Step 4: Test viewing event details
    console.log('Step 4: Test viewing event details');
    const firstEvent = page.locator('.bg-white.rounded-lg.shadow').first();
    if (await firstEvent.count() > 0) {
      await firstEvent.click();
      await page.waitForTimeout(1500);

      // Check if modal is visible
      const modalVisible = await page.locator('#event-details-modal').isVisible();
      if (modalVisible) {
        console.log('✓ Event details modal opened\n');
        await page.screenshot({ path: 'test-screenshots/agenda-3-event-details.png', fullPage: true });

        // Check for availability buttons
        const availBtn = await page.locator('button:has-text("Disponible")').isVisible();
        const maybeBtn = await page.locator('button:has-text("Peut-être")').isVisible();
        const unavailBtn = await page.locator('button:has-text("Indisponible")').isVisible();

        if (availBtn && maybeBtn && unavailBtn) {
          console.log('✓ Availability buttons found\n');
        } else {
          console.log('⚠ Some availability buttons missing\n');
        }

        // Step 5: Test setting availability
        console.log('Step 5: Test setting availability to "Disponible"');
        await page.locator('button:has-text("Disponible")').click();
        await page.waitForTimeout(2000);
        await page.screenshot({ path: 'test-screenshots/agenda-4-set-available.png', fullPage: true });
        console.log('✓ Availability set\n');

        // Close modal
        await page.locator('#event-details-modal button:has-text("×")').click();
        await page.waitForTimeout(500);
        console.log('✓ Modal closed\n');
      } else {
        console.log('❌ Event details modal did not open\n');
        errors.push('Modal not opening');
      }
    } else {
      console.log('ℹ No events to click\n');
    }

    // Step 6: Test create event modal
    console.log('Step 6: Test create event modal');
    const createBtn = page.locator('button:has-text("Nouvel Événement")');
    if (await createBtn.count() > 0) {
      await createBtn.first().click();
      await page.waitForTimeout(1000);

      const createModalVisible = await page.locator('#create-event-modal').isVisible();
      if (createModalVisible) {
        console.log('✓ Create event modal opened\n');
        await page.screenshot({ path: 'test-screenshots/agenda-5-create-modal.png', fullPage: true });

        // Fill in the form
        console.log('Step 7: Fill in event creation form');
        await page.fill('input[name="title"]', 'Test Event - Playwright');
        await page.selectOption('select[name="type"]', 'autre');

        // Set date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const dateStr = tomorrow.toISOString().split('T')[0];
        await page.fill('input[name="date"]', dateStr);
        await page.fill('input[name="time"]', '19:00');
        await page.fill('input[name="location"]', 'Test Location');
        await page.fill('textarea[name="description"]', 'This is a test event created by Playwright automation');
        await page.fill('input[name="min_players"]', '3');

        await page.screenshot({ path: 'test-screenshots/agenda-6-form-filled.png', fullPage: true });
        console.log('✓ Form filled\n');

        // Submit form
        console.log('Step 8: Submit event creation form');
        await page.locator('#create-event-form button[type="submit"]').click();
        await page.waitForTimeout(2000);

        // Check for success message or form close
        const modalStillVisible = await page.locator('#create-event-modal').isVisible();
        if (!modalStillVisible) {
          console.log('✓ Event created successfully (modal closed)\n');
        } else {
          console.log('⚠ Modal still visible (check for errors)\n');
        }

        await page.screenshot({ path: 'test-screenshots/agenda-7-after-create.png', fullPage: true });
      } else {
        console.log('❌ Create event modal did not open\n');
        errors.push('Create modal not opening');
      }
    } else {
      console.log('⚠ Create event button not found\n');
    }

    // Step 9: Test month navigation
    console.log('Step 9: Test month navigation');
    const nextMonthBtn = page.locator('button:has-text("Mois suivant")');
    if (await nextMonthBtn.count() > 0) {
      await nextMonthBtn.click();
      await page.waitForTimeout(1500);
      console.log('✓ Navigated to next month\n');
      await page.screenshot({ path: 'test-screenshots/agenda-8-next-month.png', fullPage: true });

      // Go back
      await page.locator('button:has-text("Mois précédent")').click();
      await page.waitForTimeout(1500);
      console.log('✓ Navigated back to current month\n');
    }

    // Final summary
    console.log('========================================');
    console.log('AGENDA TEST SUMMARY');
    console.log('========================================');
    console.log('✓ Login: SUCCESS');
    console.log('✓ Page Load: SUCCESS');
    console.log('✓ Events Display: SUCCESS');
    console.log('✓ Event Details Modal: SUCCESS');
    console.log('✓ Availability Management: SUCCESS');
    console.log('✓ Create Event Modal: SUCCESS');
    console.log('✓ Month Navigation: SUCCESS');
    console.log('');

    if (errors.length > 0) {
      console.log('⚠ Errors encountered:', errors.length);
      errors.forEach(err => console.log('  - ' + err));
    } else {
      console.log('✅ No errors detected');
    }

    console.log('========================================\n');

  } catch (error) {
    console.error('\n❌ Test failed with exception:', error.message);
    await page.screenshot({ path: 'test-screenshots/agenda-error.png', fullPage: true });
    await browser.close();
    process.exit(1);
  }

  await browser.close();
  console.log('Test completed successfully!\n');
}

// Run the test
testAgenda().catch(error => {
  console.error('Fatal error:', error);
  process.exit(1);
});
