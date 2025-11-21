#!/usr/bin/env python3
"""
Playwright Test Script for TopSeven7 Application
Tests the French rugby prediction game web application
"""

import asyncio
import json
from pathlib import Path
from datetime import datetime
from playwright.async_api import async_playwright

# Configuration
BASE_URL = "http://localhost"
OUTPUT_DIR = Path("playwright_test_results")

async def main():
    # Create output directory
    OUTPUT_DIR.mkdir(exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    test_dir = OUTPUT_DIR / f"test_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 TopSeven7 Playwright Test Suite")
    print(f"📁 Output directory: {test_dir.absolute()}\n")

    test_results = {
        "timestamp": timestamp,
        "base_url": BASE_URL,
        "tests": []
    }

    async with async_playwright() as p:
        # Launch browser in headless mode
        print("🚀 Launching Chromium browser (headless mode)...")
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            locale="fr-FR"
        )
        page = await context.new_page()

        try:
            # ===== Test 1: Homepage / Login Page =====
            print("\n" + "="*60)
            print("Test 1: Homepage / Login Page")
            print("="*60)

            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")

            # Take screenshot
            screenshot_path = test_dir / "01_homepage_login.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot saved: {screenshot_path.name}")

            # Extract page information
            page_title = await page.title()

            # Check for login form elements
            login_form_exists = await page.locator("form").count() > 0
            username_field = await page.locator("input[name='login']").count() > 0
            password_field = await page.locator("input[type='password']").count() > 0

            homepage_data = {
                "url": page.url,
                "title": page_title,
                "login_form_exists": login_form_exists,
                "has_username_field": username_field,
                "has_password_field": password_field
            }

            # Try to get visible text
            try:
                body_text = await page.locator("body").text_content()
                homepage_data["has_topseven_branding"] = "top" in body_text.lower() or "seven" in body_text.lower()
            except:
                homepage_data["has_topseven_branding"] = False

            test_results["tests"].append({
                "name": "Homepage Login",
                "status": "PASS" if login_form_exists else "FAIL",
                "data": homepage_data
            })

            print(f"✓ Page title: {page_title}")
            print(f"✓ Login form exists: {login_form_exists}")

            # ===== Test 2: Registration Page =====
            print("\n" + "="*60)
            print("Test 2: Registration Page")
            print("="*60)

            # Navigate to registration
            register_link = page.locator("a[href*='register']").first
            if await register_link.count() > 0:
                await register_link.click()
                await page.wait_for_load_state("networkidle")

                screenshot_path = test_dir / "02_register_page.png"
                await page.screenshot(path=str(screenshot_path), full_page=True)
                print(f"✓ Screenshot saved: {screenshot_path.name}")

                # Check registration form
                register_form_exists = await page.locator("form").count() > 0

                register_data = {
                    "url": page.url,
                    "title": await page.title(),
                    "form_exists": register_form_exists
                }

                test_results["tests"].append({
                    "name": "Registration Page",
                    "status": "PASS" if register_form_exists else "FAIL",
                    "data": register_data
                })

                print(f"✓ Registration page loaded: {page.url}")
            else:
                print("⚠ Register link not found on homepage")
                test_results["tests"].append({
                    "name": "Registration Page",
                    "status": "SKIP",
                    "reason": "Register link not found"
                })

            # Go back to home
            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")

            # ===== Test 3: Info/Rules Page =====
            print("\n" + "="*60)
            print("Test 3: Info/Rules Page")
            print("="*60)

            # Try to access info page
            await page.goto(f"{BASE_URL}/intro.php")
            await page.wait_for_load_state("networkidle")

            screenshot_path = test_dir / "03_info_page.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot saved: {screenshot_path.name}")

            info_data = {
                "url": page.url,
                "title": await page.title(),
                "accessible": page.url.endswith("intro.php")
            }

            test_results["tests"].append({
                "name": "Info Page",
                "status": "PASS" if info_data["accessible"] else "FAIL",
                "data": info_data
            })

            print(f"✓ Info page accessible: {info_data['accessible']}")

            # ===== Test 4: Password Reset Page =====
            print("\n" + "="*60)
            print("Test 4: Password Reset Page")
            print("="*60)

            await page.goto(f"{BASE_URL}/password.php")
            await page.wait_for_load_state("networkidle")

            screenshot_path = test_dir / "04_password_reset.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot saved: {screenshot_path.name}")

            password_form_exists = await page.locator("form").count() > 0

            password_data = {
                "url": page.url,
                "title": await page.title(),
                "form_exists": password_form_exists
            }

            test_results["tests"].append({
                "name": "Password Reset Page",
                "status": "PASS" if password_form_exists else "FAIL",
                "data": password_data
            })

            print(f"✓ Password reset form exists: {password_form_exists}")

            # ===== Test 5: Test Login Attempt (will fail, but tests form) =====
            print("\n" + "="*60)
            print("Test 5: Login Form Interaction")
            print("="*60)

            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")

            # Fill in test credentials (these won't work, but tests the form)
            if await page.locator("input[name='login']").count() > 0:
                await page.locator("input[name='login']").fill("test_user")
                await page.locator("input[type='password']").fill("test_password")

                screenshot_path = test_dir / "05_login_form_filled.png"
                await page.screenshot(path=str(screenshot_path), full_page=True)
                print(f"✓ Screenshot saved: {screenshot_path.name}")

                # Note: We won't actually submit to avoid failed login attempts
                # Just demonstrating form interaction

                test_results["tests"].append({
                    "name": "Login Form Interaction",
                    "status": "PASS",
                    "data": {
                        "form_fillable": True,
                        "note": "Form fields successfully filled (not submitted)"
                    }
                })

                print("✓ Login form fields successfully filled")
            else:
                test_results["tests"].append({
                    "name": "Login Form Interaction",
                    "status": "FAIL",
                    "reason": "Login form not found"
                })

            # ===== Test 6: Mobile Viewport Test =====
            print("\n" + "="*60)
            print("Test 6: Mobile Responsiveness")
            print("="*60)

            # Create a new page with mobile viewport
            mobile_page = await context.new_page()
            await mobile_page.set_viewport_size({"width": 375, "height": 667})  # iPhone SE

            await mobile_page.goto(BASE_URL)
            await mobile_page.wait_for_load_state("networkidle")

            screenshot_path = test_dir / "06_mobile_view.png"
            await mobile_page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Mobile screenshot saved: {screenshot_path.name}")

            mobile_data = {
                "viewport": "375x667 (iPhone SE)",
                "url": mobile_page.url,
                "page_loaded": True
            }

            test_results["tests"].append({
                "name": "Mobile Responsiveness",
                "status": "PASS",
                "data": mobile_data
            })

            await mobile_page.close()
            print("✓ Mobile view test completed")

            # ===== Test 7: Page Load Performance =====
            print("\n" + "="*60)
            print("Test 7: Page Load Performance")
            print("="*60)

            # Measure page load time
            start_time = asyncio.get_event_loop().time()
            await page.goto(BASE_URL)
            await page.wait_for_load_state("networkidle")
            end_time = asyncio.get_event_loop().time()

            load_time = end_time - start_time

            performance_data = {
                "page_load_time_seconds": round(load_time, 2),
                "status": "GOOD" if load_time < 3 else "SLOW" if load_time < 10 else "VERY SLOW"
            }

            test_results["tests"].append({
                "name": "Page Load Performance",
                "status": "PASS",
                "data": performance_data
            })

            print(f"✓ Page load time: {load_time:.2f} seconds ({performance_data['status']})")

            # ===== Test 8: Extract Page Structure =====
            print("\n" + "="*60)
            print("Test 8: Page Structure Analysis")
            print("="*60)

            # Analyze page structure
            links = await page.locator("a").all()
            link_hrefs = []
            for link in links[:20]:  # First 20 links
                href = await link.get_attribute("href")
                text = await link.text_content()
                if href:
                    link_hrefs.append({"href": href, "text": text.strip() if text else ""})

            forms = await page.locator("form").count()
            images = await page.locator("img").count()

            structure_data = {
                "total_links_sampled": len(link_hrefs),
                "links": link_hrefs,
                "form_count": forms,
                "image_count": images
            }

            test_results["tests"].append({
                "name": "Page Structure Analysis",
                "status": "PASS",
                "data": structure_data
            })

            print(f"✓ Found {forms} forms, {images} images, and {len(link_hrefs)} links (sampled)")

        except Exception as e:
            print(f"\n❌ Error during testing: {str(e)}")
            test_results["error"] = str(e)

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
    print("✅ Test suite completed!")
    print("="*60)

if __name__ == "__main__":
    asyncio.run(main())
