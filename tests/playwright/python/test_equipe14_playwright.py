#!/usr/bin/env python3
"""
Test Equipe 14 team selection to verify the v1/v2 undefined array key fix
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
    test_dir = OUTPUT_DIR / f"test_equipe14_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 Testing Equipe 14 Team Selection")
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

            # Click "Classement Top14" to navigate to the Top14 page
            print("Step 2: Clicking 'Classement Top14' button...")
            button = page.locator("input[value='Classement Top14']")
            if await button.count() > 0:
                await button.click()
                await page.wait_for_load_state("networkidle")
                await asyncio.sleep(1)
                print(f"✓ Navigated to: {page.url}")

                # Take screenshot
                screenshot_path = test_dir / "02_classement_top14.png"
                await page.screenshot(path=str(screenshot_path), full_page=True)
                print(f"✓ Screenshot: {screenshot_path.name}\n")

                # Check for errors on this page
                content = await page.content()
                if "Undefined array key" in content or "Warning:" in content:
                    print("⚠️  PHP Warning found on Classement Top14 page")
                else:
                    print("✓ No warnings on Classement Top14 page")

            # Look for team links (Equipe 14)
            print("\nStep 3: Finding team links (Equipe 14)...")
            team_links = await page.locator("a[href*='team14']").all()

            if len(team_links) == 0:
                # Try alternative selectors
                team_links = await page.locator("a").filter(has_text=re.compile(r"[A-Z]")).all()
                team_links = [link for link in team_links if await link.get_attribute("href") and "team14" in await link.get_attribute("href")][:5]

            print(f"Found {len(team_links)} team link(s)\n")

            if len(team_links) > 0:
                # Click the first team link
                print("Step 4: Clicking first team link...")
                first_team = team_links[0]
                team_name = await first_team.text_content()
                team_href = await first_team.get_attribute("href")

                print(f"  Team: {team_name.strip()}")
                print(f"  Link: {team_href}")

                await first_team.click()
                await page.wait_for_load_state("networkidle")
                await asyncio.sleep(2)

                print(f"✓ Navigated to: {page.url}")

                # Get page content
                content = await page.content()

                # Save HTML for inspection
                html_path = test_dir / "03_team_page.html"
                with open(html_path, "w", encoding="utf-8") as f:
                    f.write(content)
                print(f"✓ HTML saved: {html_path.name}")

                # Take screenshot
                screenshot_path = test_dir / "03_team_page.png"
                await page.screenshot(path=str(screenshot_path), full_page=True)
                print(f"✓ Screenshot: {screenshot_path.name}\n")

                # Check for the specific error
                print("Step 5: Checking for errors...")
                has_v1_error = "Undefined array key \"v1\"" in content
                has_v2_error = "Undefined array key \"v2\"" in content
                has_warnings = "Warning:" in content
                has_deprecated = "Deprecated:" in content

                if has_v1_error:
                    print("❌ FOUND: Undefined array key \"v1\" error")
                else:
                    print("✓ No 'v1' undefined array key error")

                if has_v2_error:
                    print("❌ FOUND: Undefined array key \"v2\" error")
                else:
                    print("✓ No 'v2' undefined array key error")

                if has_warnings and not has_v1_error and not has_v2_error:
                    print("⚠️  Other PHP warnings found:")
                    warnings = re.findall(r'(Warning:[^\n<]+)', content)
                    for warning in warnings[:3]:
                        clean_warning = re.sub('<[^<]+?>', '', warning)
                        print(f"  - {clean_warning[:150]}")

                if has_deprecated:
                    print("⚠️  Deprecated warnings found")

                # Final result
                print("\n" + "="*60)
                print("TEST RESULT")
                print("="*60)

                if not has_v1_error and not has_v2_error and not has_deprecated:
                    print("✅ PASS: No 'v1'/'v2' errors or deprecation warnings!")
                    print("   The Equipe 14 team page is working correctly.")
                else:
                    print("❌ FAIL: Errors or warnings found")
                    if has_v1_error or has_v2_error:
                        print("   Fix needed: v1/v2 undefined array key errors present")

            else:
                print("⚠️  No team links found. Check if there's data in the database.")

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
