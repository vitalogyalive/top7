#!/usr/bin/env python3
"""
Comprehensive test for all menu buttons to check for deprecation warnings
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
    test_dir = OUTPUT_DIR / f"test_all_menus_deprecation_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 Testing All Menu Buttons for Deprecation Warnings")
    print(f"📁 Output directory: {test_dir.absolute()}\n")

    menu_buttons = [
        "Résultats Top7",
        "Classement Top7",
        "Résultats Top14",
        "Classement Top14",
        "Final",
        "STATS Inter-EQUIPES !"
    ]

    results = []

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            locale="fr-FR"
        )
        page = await context.new_page()

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
            main_page_url = page.url

            # Test each menu button
            for idx, button_name in enumerate(menu_buttons, 1):
                print(f"\n{'='*60}")
                print(f"Test {idx}/{len(menu_buttons)}: {button_name}")
                print(f"{'='*60}")

                result = {
                    "name": button_name,
                    "has_deprecation_warning": False,
                    "has_error": False,
                    "error_details": []
                }

                try:
                    # Find and click button
                    button = page.locator(f"input[value='{button_name}']")
                    if await button.count() > 0:
                        await button.click()
                        await page.wait_for_load_state("networkidle")
                        await asyncio.sleep(1)

                        # Get page content
                        content = await page.content()

                        # Check for deprecation warnings
                        if "Deprecated:" in content:
                            result["has_deprecation_warning"] = True
                            print("⚠️  DEPRECATION WARNING FOUND!")

                            # Extract warnings
                            lines = content.split('\n')
                            for line in lines:
                                if 'Deprecated:' in line:
                                    # Clean HTML tags
                                    import re
                                    clean_line = re.sub('<[^<]+?>', '', line)
                                    result["error_details"].append(clean_line.strip())
                                    print(f"  {clean_line[:150]}")
                        else:
                            print("✓ No deprecation warnings")

                        # Check for PHP errors
                        if "Fatal error:" in content or "Warning:" in content or "Notice:" in content:
                            result["has_error"] = True
                            print("⚠️  PHP ERROR/WARNING FOUND!")

                            import re
                            errors = re.findall(r'(Fatal error:|Warning:|Notice:)[^\n<]+', content)
                            for error in errors:
                                result["error_details"].append(error)
                                print(f"  {error}")

                        # Save screenshot
                        screenshot_name = f"{idx:02d}_{button_name.lower().replace(' ', '_').replace('!', '')}.png"
                        screenshot_path = test_dir / screenshot_name
                        await page.screenshot(path=str(screenshot_path), full_page=True)

                        result["url"] = page.url
                        result["status"] = "PASS" if not result["has_deprecation_warning"] and not result["has_error"] else "FAIL"

                        print(f"✓ URL: {page.url}")
                        print(f"✓ Screenshot: {screenshot_name}")

                        # Return to main page
                        await page.goto(main_page_url)
                        await page.wait_for_load_state("networkidle")
                        await asyncio.sleep(1)

                    else:
                        print("⚠️  Button not found")
                        result["status"] = "SKIP"

                except Exception as e:
                    print(f"❌ Error: {str(e)}")
                    result["status"] = "ERROR"
                    result["error_details"].append(str(e))

                results.append(result)

        except Exception as e:
            print(f"\n❌ Critical error: {str(e)}")

        finally:
            await browser.close()

    # Print summary
    print(f"\n\n{'='*60}")
    print("TEST SUMMARY")
    print(f"{'='*60}\n")

    total = len(results)
    passed = sum(1 for r in results if r["status"] == "PASS")
    failed = sum(1 for r in results if r["status"] == "FAIL")
    errors = sum(1 for r in results if r["status"] == "ERROR")
    skipped = sum(1 for r in results if r["status"] == "SKIP")

    print(f"Total menus tested: {total}")
    print(f"✓ Passed: {passed}")
    if failed > 0:
        print(f"✗ Failed: {failed}")
    if errors > 0:
        print(f"⚠ Errors: {errors}")
    if skipped > 0:
        print(f"⊝ Skipped: {skipped}")

    print("\nDetailed Results:")
    for r in results:
        status_icon = "✓" if r["status"] == "PASS" else "✗" if r["status"] == "FAIL" else "⚠" if r["status"] == "ERROR" else "⊝"
        print(f"  {status_icon} {r['name']}: {r['status']}")
        if r.get("has_deprecation_warning"):
            print(f"    └─ Has deprecation warnings")
        if r.get("has_error"):
            print(f"    └─ Has PHP errors/warnings")

    if failed > 0 or errors > 0:
        print("\n⚠️  Some menus have issues. Check screenshots for details.")
    else:
        print("\n✅ All menus are working without deprecation warnings!")

    print(f"\n📁 Results saved to: {test_dir.absolute()}")

if __name__ == "__main__":
    asyncio.run(main())
