#!/usr/bin/env python3
"""
Test Final menu to verify the "Undefined array key 0" fix
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
    test_dir = OUTPUT_DIR / f"test_final_menu_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 Testing Final Menu")
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

            print(f"✓ Logged in: {page.url}")

            # Take screenshot
            screenshot_path = test_dir / "01_logged_in.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot: {screenshot_path.name}\n")

            # Click "Final" button
            print("Step 2: Clicking 'Final' button...")
            button = page.locator("input[value='Final']")

            if await button.count() > 0:
                await button.click()
                await page.wait_for_load_state("networkidle")
                await asyncio.sleep(2)

                print(f"✓ Navigated to: {page.url}")

                # Get page content
                content = await page.content()

                # Save HTML for inspection
                html_path = test_dir / "02_final_page.html"
                with open(html_path, "w", encoding="utf-8") as f:
                    f.write(content)
                print(f"✓ HTML saved: {html_path.name}")

                # Take screenshot
                screenshot_path = test_dir / "02_final_page.png"
                await page.screenshot(path=str(screenshot_path), full_page=True)
                print(f"✓ Screenshot: {screenshot_path.name}\n")

                # Check for errors
                print("Step 3: Checking for errors...")
                has_array_key_error = "Undefined array key" in content
                has_array_key_0_error = 'Undefined array key "0"' in content or "Undefined array key '0'" in content
                has_warnings = "Warning:" in content
                has_deprecated = "Deprecated:" in content

                if has_array_key_0_error:
                    print("❌ FOUND: Undefined array key '0' error")
                    # Extract the error
                    errors = re.findall(r'(Undefined array key[^\n<]+)', content)
                    for error in errors[:3]:
                        clean_error = re.sub('<[^<]+?>', '', error)
                        print(f"  - {clean_error}")
                else:
                    print("✓ No 'Undefined array key 0' error")

                if has_array_key_error and not has_array_key_0_error:
                    print("⚠️  Other 'Undefined array key' errors found:")
                    errors = re.findall(r'(Undefined array key[^\n<]+)', content)
                    for error in errors[:3]:
                        clean_error = re.sub('<[^<]+?>', '', error)
                        print(f"  - {clean_error}")

                if has_warnings and not has_array_key_error:
                    print("⚠️  Other PHP warnings found:")
                    warnings = re.findall(r'(Warning:[^\n<]+)', content)
                    for warning in warnings[:3]:
                        clean_warning = re.sub('<[^<]+?>', '', warning)
                        print(f"  - {clean_warning[:150]}")

                if has_deprecated:
                    print("⚠️  Deprecated warnings found")
                    deprecated = re.findall(r'(Deprecated:[^\n<]+)', content)
                    for dep in deprecated[:3]:
                        clean_dep = re.sub('<[^<]+?>', '', dep)
                        print(f"  - {clean_dep[:150]}")

                # Check page title
                page_title = await page.title()
                print(f"\n✓ Page title: {page_title}")

                # Final result
                print("\n" + "="*60)
                print("TEST RESULT")
                print("="*60)

                if not has_array_key_0_error and not has_deprecated:
                    print("✅ PASS: No 'Undefined array key 0' errors!")
                    print("   The Final menu is working correctly.")
                else:
                    print("❌ FAIL: Errors or warnings found")
                    if has_array_key_0_error:
                        print("   Fix needed: 'Undefined array key 0' error present")

            else:
                print("⚠️  'Final' button not found")

        except Exception as e:
            print(f"\n❌ Error: {str(e)}")
            import traceback
            traceback.print_exc()

            # Error screenshot
            try:
                error_screenshot = test_dir / "ERROR.png"
                await page.screenshot(path=str(error_screenshot), full_page=True)
                print(f"📸 Error screenshot: {error_screenshot.name}")
            except:
                pass

        finally:
            await browser.close()

    print(f"\n📁 Results saved to: {test_dir.absolute()}")

if __name__ == "__main__":
    asyncio.run(main())
