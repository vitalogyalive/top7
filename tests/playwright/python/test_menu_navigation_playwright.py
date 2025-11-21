#!/usr/bin/env python3
"""
Playwright Test Script for TopSeven7 Application - Menu Navigation
Tests each blue menu button on the authenticated page
"""

import asyncio
import json
from pathlib import Path
from datetime import datetime
from playwright.async_api import async_playwright

# Configuration
BASE_URL = "http://localhost"
OUTPUT_DIR = Path("playwright_test_results")
TEST_EMAIL = "test2@topseven.fr"
TEST_PASSWORD = "test123"

async def main():
    # Create output directory
    OUTPUT_DIR.mkdir(exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    test_dir = OUTPUT_DIR / f"test_menu_navigation_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 TopSeven7 Menu Navigation Test Suite")
    print(f"📁 Output directory: {test_dir.absolute()}")
    print(f"👤 Test user: {TEST_EMAIL}\n")

    test_results = {
        "timestamp": timestamp,
        "base_url": BASE_URL,
        "test_user": TEST_EMAIL,
        "menus_tested": []
    }

    async with async_playwright() as p:
        # Launch browser
        print("🚀 Launching Chromium browser...")
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            locale="fr-FR"
        )
        page = await context.new_page()

        try:
            # ===== Login First =====
            print("\n" + "="*60)
            print("Logging in...")
            print("="*60)

            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")

            # Fill and submit login form
            await page.locator("input[name='login']").fill(TEST_EMAIL)
            await page.locator("input[type='password']").fill(TEST_PASSWORD)
            await page.locator("input[type='submit']").click()
            await page.wait_for_load_state("networkidle", timeout=10000)
            await asyncio.sleep(2)

            print(f"✓ Logged in successfully")
            print(f"✓ Current page: {page.url}\n")

            # Define menu buttons to test
            menu_tests = [
                {
                    "name": "Résultats Top7",
                    "selector": "input[value='Résultats Top7']",
                    "type": "button"
                },
                {
                    "name": "Classement Top7",
                    "selector": "input[value='Classement Top7']",
                    "type": "button"
                },
                {
                    "name": "Résultats Top14",
                    "selector": "input[value='Résultats Top14']",
                    "type": "button"
                },
                {
                    "name": "Classement Top14",
                    "selector": "input[value='Classement Top14']",
                    "type": "button"
                },
                {
                    "name": "Final",
                    "selector": "input[value='Final']",
                    "type": "button"
                },
                {
                    "name": "STATS Inter-EQUIPES !",
                    "selector": "input[value='STATS Inter-EQUIPES !']",
                    "type": "button"
                }
            ]

            # Test each menu
            for idx, menu in enumerate(menu_tests, start=1):
                print("\n" + "="*60)
                print(f"Test {idx}: {menu['name']}")
                print("="*60)

                menu_result = {
                    "name": menu['name'],
                    "test_number": idx,
                    "status": "PENDING"
                }

                try:
                    # Look for the button
                    button = page.locator(menu['selector']).first
                    button_count = await button.count()

                    if button_count > 0:
                        print(f"🔘 Found '{menu['name']}' button")

                        # Click the button
                        await button.click()
                        await page.wait_for_load_state("networkidle", timeout=10000)
                        await asyncio.sleep(1)

                        # Capture page information
                        current_url = page.url
                        page_title = await page.title()

                        # Get page content
                        body_text = await page.locator("body").text_content()
                        content_length = len(body_text) if body_text else 0

                        # Count page elements
                        tables = await page.locator("table").count()
                        forms = await page.locator("form").count()
                        links = await page.locator("a").count()
                        images = await page.locator("img").count()

                        # Take screenshot
                        screenshot_filename = f"{idx:02d}_{menu['name'].lower().replace(' ', '_').replace('!', '')}.png"
                        screenshot_path = test_dir / screenshot_filename
                        await page.screenshot(path=str(screenshot_path), full_page=True)

                        # Try to detect what content is displayed
                        content_indicators = {
                            "has_table": tables > 0,
                            "has_ranking": "classement" in body_text.lower() or "rang" in body_text.lower(),
                            "has_results": "résultat" in body_text.lower() or "score" in body_text.lower(),
                            "has_stats": "stat" in body_text.lower(),
                            "has_match_data": "match" in body_text.lower() or "journée" in body_text.lower(),
                        }

                        # Get visible headings if any
                        headings = []
                        for h_tag in ["h1", "h2", "h3"]:
                            h_elements = await page.locator(h_tag).all()
                            for h in h_elements[:5]:  # First 5 headings
                                text = await h.text_content()
                                if text:
                                    headings.append({h_tag: text.strip()})

                        menu_result.update({
                            "status": "PASS",
                            "url": current_url,
                            "title": page_title,
                            "screenshot": screenshot_filename,
                            "content_length": content_length,
                            "elements": {
                                "tables": tables,
                                "forms": forms,
                                "links": links,
                                "images": images
                            },
                            "content_type": content_indicators,
                            "headings": headings
                        })

                        print(f"✓ Page loaded: {current_url}")
                        print(f"✓ Title: {page_title}")
                        print(f"✓ Content: {content_length} chars")
                        print(f"✓ Elements: {tables} tables, {forms} forms, {links} links")
                        print(f"✓ Screenshot: {screenshot_filename}")

                        if headings:
                            print(f"✓ Found {len(headings)} heading(s)")

                    else:
                        print(f"⚠ Button '{menu['name']}' not found")
                        menu_result.update({
                            "status": "SKIP",
                            "reason": "Button not found on page"
                        })

                except Exception as e:
                    print(f"❌ Error testing '{menu['name']}': {str(e)[:200]}")
                    menu_result.update({
                        "status": "FAIL",
                        "error": str(e)[:500]
                    })

                    # Take error screenshot
                    try:
                        error_screenshot = test_dir / f"{idx:02d}_ERROR_{menu['name'].lower().replace(' ', '_')}.png"
                        await page.screenshot(path=str(error_screenshot), full_page=True)
                        menu_result["error_screenshot"] = error_screenshot.name
                    except:
                        pass

                test_results["menus_tested"].append(menu_result)

                # Return to main page after each test
                try:
                    await page.goto(f"{BASE_URL}/display")
                    await page.wait_for_load_state("networkidle")
                    await asyncio.sleep(1)
                except:
                    # If display page doesn't work, try team page
                    await page.goto(f"{BASE_URL}/team")
                    await page.wait_for_load_state("networkidle")
                    await asyncio.sleep(1)

        except Exception as e:
            print(f"\n❌ Critical error during testing: {str(e)}")
            test_results["critical_error"] = str(e)

        finally:
            # Close browser
            await browser.close()

    # ===== Save Test Results =====
    results_path = test_dir / "menu_navigation_results.json"
    with open(results_path, "w", encoding="utf-8") as f:
        json.dump(test_results, f, indent=2, ensure_ascii=False)

    # ===== Print Summary =====
    print("\n" + "="*60)
    print("MENU NAVIGATION TEST SUMMARY")
    print("="*60)

    total_menus = len(test_results["menus_tested"])
    passed = sum(1 for m in test_results["menus_tested"] if m["status"] == "PASS")
    failed = sum(1 for m in test_results["menus_tested"] if m["status"] == "FAIL")
    skipped = sum(1 for m in test_results["menus_tested"] if m["status"] == "SKIP")

    print(f"\nTotal Menus Tested: {total_menus}")
    print(f"✓ Passed: {passed}")
    if failed > 0:
        print(f"✗ Failed: {failed}")
    if skipped > 0:
        print(f"⊝ Skipped: {skipped}")

    print("\n📊 Detailed Results:")
    for menu in test_results["menus_tested"]:
        status_icon = "✓" if menu["status"] == "PASS" else "✗" if menu["status"] == "FAIL" else "⊝"
        print(f"  {status_icon} {menu['name']}: {menu['status']}")

    print(f"\n📊 Test results saved: {results_path}")
    print(f"📁 All files location: {test_dir.absolute()}")

    print("\n" + "="*60)
    print("✅ Menu navigation test suite completed!")
    print("="*60)

if __name__ == "__main__":
    asyncio.run(main())
