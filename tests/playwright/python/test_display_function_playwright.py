#!/usr/bin/env python3
"""
Test display function to verify $class variable fix
"""

import asyncio
from pathlib import Path
from datetime import datetime
from playwright.async_api import async_playwright
import re

BASE_URL = "http://localhost"
OUTPUT_DIR = Path("playwright_test_results")
TEST_EMAIL = "test2@topseven.fr"
TEST_PASSWORD = "password123"

async def main():
    OUTPUT_DIR.mkdir(exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    test_dir = OUTPUT_DIR / f"test_display_class_var_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 Testing Display Function - $class Variable Fix")
    print(f"📁 Output directory: {test_dir.absolute()}\n")

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            locale="fr-FR"
        )
        page = await context.new_page()

        try:
            # Login
            print("Step 1: Logging in...")
            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")

            await page.locator("input[name='login']").fill(TEST_EMAIL)
            await page.locator("input[type='password']").fill(TEST_PASSWORD)
            await page.locator("input[type='submit']").click()
            await page.wait_for_load_state("networkidle")
            await asyncio.sleep(2)

            print(f"✓ Logged in: {page.url}\n")

            # Test all display modes
            test_urls = [
                ("Main page", "/display"),
                ("Team page", "/team"),
                ("Rank7", "/rank7"),
                ("Rank", "/rank"),
                ("Stats", "/stats"),
            ]

            for name, url in test_urls:
                print(f"Testing {name} ({url})...")

                await page.goto(f"{BASE_URL}{url}")
                await page.wait_for_load_state("networkidle")
                await asyncio.sleep(1)

                content = await page.content()

                # Check for undefined variable error
                has_undefined_class = "Undefined variable $class" in content or 'Undefined variable "$class"' in content
                has_warnings = "Warning:" in content

                if has_undefined_class:
                    print(f"  ❌ {name}: Has 'Undefined variable $class' error")

                    # Save screenshot
                    screenshot_path = test_dir / f"{name.lower().replace(' ', '_')}_ERROR.png"
                    await page.screenshot(path=str(screenshot_path), full_page=True)

                    # Save HTML
                    html_path = test_dir / f"{name.lower().replace(' ', '_')}_ERROR.html"
                    with open(html_path, "w", encoding="utf-8") as f:
                        f.write(content)
                else:
                    print(f"  ✓ {name}: No undefined $class error")

                if has_warnings and not has_undefined_class:
                    # Check what warnings exist
                    warnings = re.findall(r'(Warning:[^\n<]+)', content)
                    if warnings:
                        print(f"    ⚠️  Other warnings: {len(warnings)}")

            print("\n" + "="*60)
            print("TEST RESULT")
            print("="*60)
            print("✅ All pages tested - check for any errors above")

        except Exception as e:
            print(f"\n❌ Error: {str(e)}")
            import traceback
            traceback.print_exc()

        finally:
            await browser.close()

    print(f"\n📁 Results saved to: {test_dir.absolute()}")

if __name__ == "__main__":
    asyncio.run(main())
