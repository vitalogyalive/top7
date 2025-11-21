#!/usr/bin/env python3
"""
Direct access test for team14 to verify v1/v2 fix
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
    test_dir = OUTPUT_DIR / f"test_direct_team14_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 Direct Team14 Access Test")
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
            print("Logging in...")
            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")

            await page.locator("input[name='login']").fill(TEST_EMAIL)
            await page.locator("input[type='password']").fill(TEST_PASSWORD)
            await page.locator("input[type='submit']").click()
            await page.wait_for_load_state("networkidle")
            await asyncio.sleep(2)

            print(f"✓ Logged in: {page.url}\n")

            # Try accessing team14 page directly with a team ID
            # Let's try team IDs 1-5
            for team_id in range(1, 6):
                print(f"Testing team ID {team_id}...")
                url = f"{BASE_URL}/team14?top14team={team_id}"

                await page.goto(url)
                await page.wait_for_load_state("networkidle")
                await asyncio.sleep(1)

                content = await page.content()

                # Check for the error
                has_v1_error = "Undefined array key \"v1\"" in content
                has_v2_error = "Undefined array key \"v2\"" in content

                if has_v1_error or has_v2_error:
                    print(f"  ❌ Team {team_id}: Has v1/v2 errors")

                    # Save screenshot
                    screenshot_path = test_dir / f"team{team_id}_ERROR.png"
                    await page.screenshot(path=str(screenshot_path), full_page=True)

                    # Save HTML
                    html_path = test_dir / f"team{team_id}_ERROR.html"
                    with open(html_path, "w", encoding="utf-8") as f:
                        f.write(content)

                    break  # Found an error, stop testing
                else:
                    print(f"  ✓ Team {team_id}: No v1/v2 errors")
            else:
                print("\n✅ All tested teams work without v1/v2 errors!")

        except Exception as e:
            print(f"\n❌ Error: {str(e)}")

        finally:
            await browser.close()

    print(f"\n📁 Results: {test_dir.absolute()}")

if __name__ == "__main__":
    asyncio.run(main())
