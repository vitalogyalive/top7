const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  const page = await context.newPage();

  console.log('🌐 Navigating to http://localhost/...');
  await page.goto('http://localhost/');
  await page.waitForLoadState('networkidle');

  // Take screenshot of login page
  console.log('📸 Taking screenshot of login page...');
  await page.screenshot({ path: 'login-page.png', fullPage: true });

  // Check if Tailwind CSS is loaded
  const tailwindLoaded = await page.evaluate(() => {
    const link = document.querySelector('link[href*="styles/output.css"]');
    return link !== null;
  });
  console.log(`✅ Tailwind CSS loaded: ${tailwindLoaded}`);

  // Check for modern CSS properties
  const loginBox = await page.evaluate(() => {
    const box = document.querySelector('.loginbox1');
    if (!box) return null;

    const styles = window.getComputedStyle(box);
    return {
      backgroundColor: styles.backgroundColor,
      backdropFilter: styles.backdropFilter,
      borderRadius: styles.borderRadius,
      boxShadow: styles.boxShadow,
      padding: styles.padding
    };
  });

  console.log('🎨 Login box styles:', JSON.stringify(loginBox, null, 2));

  // Check background gradient
  const htmlBackground = await page.evaluate(() => {
    const html = document.documentElement;
    const styles = window.getComputedStyle(html);
    return {
      backgroundImage: styles.backgroundImage
    };
  });

  console.log('🎨 HTML background:', htmlBackground.backgroundImage.substring(0, 100) + '...');

  // Test responsive design at mobile viewport
  console.log('📱 Testing mobile viewport (375x667)...');
  await page.setViewportSize({ width: 375, height: 667 });
  await page.screenshot({ path: 'login-page-mobile.png', fullPage: true });

  // Check mobile layout
  const mobileBox = await page.evaluate(() => {
    const box = document.querySelector('.loginbox1');
    if (!box) return null;
    const rect = box.getBoundingClientRect();
    return {
      width: rect.width,
      height: rect.height
    };
  });
  console.log('📱 Mobile login box dimensions:', mobileBox);

  // Test tablet viewport
  console.log('📱 Testing tablet viewport (768x1024)...');
  await page.setViewportSize({ width: 768, height: 1024 });
  await page.screenshot({ path: 'login-page-tablet.png', fullPage: true });

  console.log('✅ All tests completed!');
  console.log('📄 Screenshots saved:');
  console.log('   - login-page.png (desktop)');
  console.log('   - login-page-mobile.png (mobile)');
  console.log('   - login-page-tablet.png (tablet)');

  await browser.close();
})();
