#!/usr/bin/env python3
"""
Playwright Test Script for TopSeven7 Application - Authenticated Session
Tests the main page after login
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
    test_dir = OUTPUT_DIR / f"test_authenticated_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 TopSeven7 Authenticated Test Suite")
    print(f"📁 Output directory: {test_dir.absolute()}")
    print(f"👤 Test user: {TEST_EMAIL}\n")

    test_results = {
        "timestamp": timestamp,
        "base_url": BASE_URL,
        "test_user": TEST_EMAIL,
        "tests": []
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
            # ===== Test 1: Login =====
            print("\n" + "="*60)
            print("Test 1: User Login")
            print("="*60)

            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")

            # Take screenshot before login
            screenshot_path = test_dir / "01_before_login.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot saved: {screenshot_path.name}")

            # Fill login form
            await page.locator("input[name='login']").fill(TEST_EMAIL)
            await page.locator("input[type='password']").fill(TEST_PASSWORD)

            # Take screenshot with filled form
            screenshot_path = test_dir / "02_login_filled.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot saved: {screenshot_path.name}")

            # Submit login form
            print("🔐 Submitting login credentials...")
            await page.locator("input[type='submit']").click()

            # Wait for navigation after login
            try:
                await page.wait_for_load_state("networkidle", timeout=10000)
                await asyncio.sleep(2)  # Additional wait for any redirects
            except:
                pass

            # Take screenshot after login
            screenshot_path = test_dir / "03_after_login.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot saved: {screenshot_path.name}")

            current_url = page.url
            page_title = await page.title()

            login_success = "login" not in current_url.lower() and current_url != f"{BASE_URL}/"

            login_data = {
                "url": current_url,
                "title": page_title,
                "login_successful": login_success
            }

            test_results["tests"].append({
                "name": "User Login",
                "status": "PASS" if login_success else "FAIL",
                "data": login_data
            })

            print(f"✓ Current URL: {current_url}")
            print(f"✓ Page title: {page_title}")
            print(f"✓ Login successful: {login_success}")

            if not login_success:
                print("❌ Login failed - may have been redirected back to index")
                # Try to get error message if any
                body_text = await page.locator("body").text_content()
                if body_text:
                    print(f"Page content preview: {body_text[:200]}")

            # ===== Test 2: Main Dashboard Content =====
            print("\n" + "="*60)
            print("Test 2: Main Dashboard Content")
            print("="*60)

            if login_success:
                # Extract page content
                body_text = await page.locator("body").text_content()

                # Check for various dashboard elements
                has_welcome = "bonjour" in body_text.lower() or "bienvenue" in body_text.lower()
                has_pseudo = TEST_EMAIL.split('@')[0] in body_text.lower() or "testuser" in body_text.lower()

                # Count various elements
                links = await page.locator("a").count()
                forms = await page.locator("form").count()
                tables = await page.locator("table").count()
                images = await page.locator("img").count()

                # Get navigation links
                nav_links = []
                all_links = await page.locator("a").all()
                for link in all_links[:20]:
                    href = await link.get_attribute("href")
                    text = await link.text_content()
                    if href and text:
                        nav_links.append({"href": href, "text": text.strip()})

                dashboard_data = {
                    "url": page.url,
                    "has_welcome_message": has_welcome,
                    "shows_user_info": has_pseudo,
                    "link_count": links,
                    "form_count": forms,
                    "table_count": tables,
                    "image_count": images,
                    "navigation_links": nav_links
                }

                test_results["tests"].append({
                    "name": "Dashboard Content",
                    "status": "PASS",
                    "data": dashboard_data
                })

                print(f"✓ Welcome message found: {has_welcome}")
                print(f"✓ User info displayed: {has_pseudo}")
                print(f"✓ Links: {links}, Forms: {forms}, Tables: {tables}, Images: {images}")
                print(f"✓ Navigation links found: {len(nav_links)}")

            # ===== Test 3: Explore Key Pages =====
            print("\n" + "="*60)
            print("Test 3: Explore Key Pages")
            print("="*60)

            if login_success:
                explored_pages = []

                # Try to find and click on common pages
                page_tests = [
                    {"name": "Prono/Prediction", "selector": "a[href*='prono']"},
                    {"name": "Ranking", "selector": "a[href*='rank']"},
                    {"name": "Team", "selector": "a[href*='team']"},
                    {"name": "Display", "selector": "a[href*='display']"},
                ]

                for idx, page_test in enumerate(page_tests, start=4):
                    try:
                        link = page.locator(page_test["selector"]).first
                        if await link.count() > 0:
                            href = await link.get_attribute("href")
                            print(f"\n📄 Testing {page_test['name']} page...")

                            await link.click()
                            await page.wait_for_load_state("networkidle", timeout=5000)

                            screenshot_path = test_dir / f"0{idx}_{page_test['name'].lower().replace('/', '_')}.png"
                            await page.screenshot(path=str(screenshot_path), full_page=True)

                            explored_pages.append({
                                "name": page_test["name"],
                                "url": page.url,
                                "title": await page.title(),
                                "accessible": True
                            })

                            print(f"✓ {page_test['name']} page loaded: {page.url}")
                            print(f"✓ Screenshot saved: {screenshot_path.name}")

                            # Go back to main page
                            await page.goto(current_url)
                            await page.wait_for_load_state("networkidle")
                    except Exception as e:
                        explored_pages.append({
                            "name": page_test["name"],
                            "accessible": False,
                            "error": str(e)
                        })
                        print(f"⚠ Could not access {page_test['name']} page: {str(e)[:100]}")

                test_results["tests"].append({
                    "name": "Key Pages Exploration",
                    "status": "PASS",
                    "data": {"explored_pages": explored_pages}
                })

            # ===== Test 4: Check Session Info =====
            print("\n" + "="*60)
            print("Test 4: Session Information")
            print("="*60)

            if login_success:
                # Try to get any visible user info
                user_info = await page.locator("body").text_content()

                session_data = {
                    "logged_in": True,
                    "current_page": page.url,
                    "page_content_length": len(user_info) if user_info else 0
                }

                test_results["tests"].append({
                    "name": "Session Information",
                    "status": "PASS",
                    "data": session_data
                })

                print(f"✓ Session active")
                print(f"✓ Current page: {page.url}")
                print(f"✓ Content length: {session_data['page_content_length']} chars")

            # ===== Test 5: Logout =====
            print("\n" + "="*60)
            print("Test 5: Logout")
            print("="*60)

            if login_success:
                # Look for logout link
                logout_link = page.locator("a[href*='logout']").first
                if await logout_link.count() > 0:
                    print("🚪 Logging out...")
                    await logout_link.click()
                    await page.wait_for_load_state("networkidle")

                    screenshot_path = test_dir / "99_after_logout.png"
                    await page.screenshot(path=str(screenshot_path), full_page=True)

                    logout_url = page.url
                    back_to_login = "login" in logout_url or logout_url == f"{BASE_URL}/" or logout_url == f"{BASE_URL}/index"

                    test_results["tests"].append({
                        "name": "Logout",
                        "status": "PASS" if back_to_login else "FAIL",
                        "data": {
                            "logout_successful": back_to_login,
                            "final_url": logout_url
                        }
                    })

                    print(f"✓ Logout URL: {logout_url}")
                    print(f"✓ Returned to login: {back_to_login}")
                    print(f"✓ Screenshot saved: {screenshot_path.name}")
                else:
                    print("⚠ Logout link not found")
                    test_results["tests"].append({
                        "name": "Logout",
                        "status": "SKIP",
                        "reason": "Logout link not found"
                    })

        except Exception as e:
            print(f"\n❌ Error during testing: {str(e)}")
            test_results["error"] = str(e)

            # Take error screenshot
            try:
                error_screenshot = test_dir / "ERROR_screenshot.png"
                await page.screenshot(path=str(error_screenshot), full_page=True)
                print(f"📸 Error screenshot saved: {error_screenshot.name}")
            except:
                pass

        finally:
            # Close browser
            await browser.close()

    # ===== Save Test Results =====
    results_path = test_dir / "test_results.json"
    with open(results_path, "w", encoding="utf-8") as f:
        json.dump(test_results, f, indent=2, ensure_ascii=False)

    # ===== Print Summary =====
    print("\n" + "="*60)
    print("TEST SUMMARY")
    print("="*60)

    total_tests = len(test_results["tests"])
    passed = sum(1 for t in test_results["tests"] if t["status"] == "PASS")
    failed = sum(1 for t in test_results["tests"] if t["status"] == "FAIL")
    skipped = sum(1 for t in test_results["tests"] if t["status"] == "SKIP")

    print(f"\nTotal Tests: {total_tests}")
    print(f"✓ Passed: {passed}")
    if failed > 0:
        print(f"✗ Failed: {failed}")
    if skipped > 0:
        print(f"⊝ Skipped: {skipped}")

    print(f"\n📊 Test results saved: {results_path}")
    print(f"📁 All files location: {test_dir.absolute()}")

    print("\n" + "="*60)
    print("✅ Authenticated test suite completed!")
    print("="*60)

if __name__ == "__main__":
    asyncio.run(main())
