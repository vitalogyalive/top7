#!/usr/bin/env python3
"""
Test Résultat Top 14 menu to reproduce the deprecation warning
"""

import asyncio
from pathlib import Path
from datetime import datetime
from playwright.async_api import async_playwright

BASE_URL = "http://localhost"
OUTPUT_DIR = Path("playwright_test_results")
TEST_EMAIL = "test2@topseven.fr"
TEST_PASSWORD = "password123"

async def main():
    OUTPUT_DIR.mkdir(exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    test_dir = OUTPUT_DIR / f"test_top14_menu_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 Testing Résultat Top 14 Menu")
    print(f"📁 Output directory: {test_dir.absolute()}\n")

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=False)
        context = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            locale="fr-FR"
        )
        page = await context.new_page()

        # Enable console logging
        page.on("console", lambda msg: print(f"Console: {msg.text}"))

        try:
            # Login
            print("Logging in...")
            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")

            await page.locator("input[name='login']").fill(TEST_EMAIL)
            await page.locator("input[type='password']").fill(TEST_PASSWORD)
            await page.locator("input[type='submit']").click()
            await page.wait_for_load_state("networkidle")
            await asyncio.sleep(2)

            print(f"✓ Logged in: {page.url}\n")

            # Take screenshot before clicking
            screenshot_path = test_dir / "01_before_top14_click.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot: {screenshot_path.name}")

            # Click "Résultats Top14" button
            print("\nClicking 'Résultats Top14' button...")
            button = page.locator("input[value='Résultats Top14']")
            if await button.count() > 0:
                await button.click()
                await page.wait_for_load_state("networkidle")
                await asyncio.sleep(2)

                # Capture page content
                content = await page.content()

                # Save HTML
                html_path = test_dir / "02_top14_page.html"
                with open(html_path, "w", encoding="utf-8") as f:
                    f.write(content)
                print(f"✓ HTML saved: {html_path.name}")

                # Take screenshot
                screenshot_path = test_dir / "02_top14_page.png"
                await page.screenshot(path=str(screenshot_path), full_page=True)
                print(f"✓ Screenshot: {screenshot_path.name}")

                # Check for errors in content
                if "Deprecated:" in content:
                    print("\n⚠️  DEPRECATION WARNING FOUND!")
                    # Extract the warning
                    lines = content.split('\n')
                    for i, line in enumerate(lines):
                        if 'Deprecated:' in line:
                            print(f"  Line {i}: {line[:200]}")
                else:
                    print("\n✓ No deprecation warnings found in HTML")

                print(f"\n✓ Current URL: {page.url}")
                print(f"✓ Page title: {await page.title()}")

            else:
                print("⚠️  Button 'Résultats Top14' not found")

        except Exception as e:
            print(f"\n❌ Error: {str(e)}")
            error_screenshot = test_dir / "ERROR.png"
            await page.screenshot(path=str(error_screenshot), full_page=True)

        finally:
            await browser.close()

    print(f"\n📁 Results saved to: {test_dir.absolute()}")

if __name__ == "__main__":
    asyncio.run(main())
