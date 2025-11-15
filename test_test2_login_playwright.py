#!/usr/bin/env python3
"""
Playwright Test Script - test2 User Login Verification
Tests both login failure (wrong password) and login success (correct password)
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
WRONG_PASSWORD = "test123"      # This is WRONG (used in existing tests)
CORRECT_PASSWORD = "password123"  # This is CORRECT (from migrations)

async def test_login(page, email, password, test_name, test_dir, screenshot_prefix):
    """Test login with given credentials and return result"""
    print(f"\n{'='*60}")
    print(f"Testing: {test_name}")
    print(f"{'='*60}")
    print(f"Email: {email}")
    print(f"Password: {password}")

    result = {
        "test_name": test_name,
        "email": email,
        "password": password,
        "timestamp": datetime.now().isoformat()
    }

    try:
        # Navigate to homepage (should redirect to login)
        await page.goto(BASE_URL)
        await page.wait_for_load_state("networkidle")

        # Screenshot before login
        screenshot_path = test_dir / f"{screenshot_prefix}_01_before_login.png"
        await page.screenshot(path=str(screenshot_path), full_page=True)
        print(f"✓ Screenshot: {screenshot_path.name}")

        initial_url = page.url
        result["initial_url"] = initial_url

        # Fill login form
        await page.locator("input[name='login']").fill(email)
        await page.locator("input[type='password']").fill(password)

        # Screenshot with filled form
        screenshot_path = test_dir / f"{screenshot_prefix}_02_form_filled.png"
        await page.screenshot(path=str(screenshot_path), full_page=True)
        print(f"✓ Screenshot: {screenshot_path.name}")

        # Submit login form
        print("🔐 Submitting login credentials...")
        await page.locator("input[type='submit']").click()

        # Wait for response
        try:
            await page.wait_for_load_state("networkidle", timeout=10000)
            await asyncio.sleep(2)  # Additional wait for redirects
        except:
            pass

        # Screenshot after login attempt
        screenshot_path = test_dir / f"{screenshot_prefix}_03_after_submit.png"
        await page.screenshot(path=str(screenshot_path), full_page=True)
        print(f"✓ Screenshot: {screenshot_path.name}")

        # Analyze the result
        current_url = page.url
        page_title = await page.title()
        body_text = await page.locator("body").text_content()

        result["final_url"] = current_url
        result["page_title"] = page_title
        result["body_length"] = len(body_text) if body_text else 0

        # Determine if login was successful
        # Successful login should redirect away from index/login page
        is_on_login_page = (
            current_url == f"{BASE_URL}/" or
            current_url == f"{BASE_URL}/index.php" or
            "login" in current_url.lower()
        )

        login_successful = not is_on_login_page
        result["login_successful"] = login_successful

        # Check for error messages
        error_indicators = []
        if body_text:
            if "erreur" in body_text.lower():
                error_indicators.append("Found 'erreur' in page")
            if "incorrect" in body_text.lower():
                error_indicators.append("Found 'incorrect' in page")
            if "invalid" in body_text.lower():
                error_indicators.append("Found 'invalid' in page")
            if "échec" in body_text.lower():
                error_indicators.append("Found 'échec' in page")

        result["error_indicators"] = error_indicators

        # Print results
        print(f"\n📊 Results:")
        print(f"  Initial URL: {initial_url}")
        print(f"  Final URL:   {current_url}")
        print(f"  Page Title:  {page_title}")
        print(f"  Login Successful: {login_successful}")

        if error_indicators:
            print(f"  Error Indicators: {', '.join(error_indicators)}")

        # If login was successful, gather some session info
        if login_successful:
            print(f"\n✅ LOGIN SUCCESSFUL!")

            # Try to find user info on the page
            has_logout = await page.locator("a[href*='logout']").count() > 0
            has_user_menu = await page.locator("a[href*='user']").count() > 0

            result["session_indicators"] = {
                "has_logout_link": has_logout,
                "has_user_menu": has_user_menu,
                "authenticated_page_content_length": len(body_text) if body_text else 0
            }

            print(f"  Has logout link: {has_logout}")
            print(f"  Has user menu: {has_user_menu}")

            # Take screenshot of authenticated page
            screenshot_path = test_dir / f"{screenshot_prefix}_04_authenticated_page.png"
            await page.screenshot(path=str(screenshot_path), full_page=True)
            print(f"✓ Screenshot: {screenshot_path.name}")

            # Logout for next test
            if has_logout:
                print("\n🚪 Logging out...")
                await page.locator("a[href*='logout']").click()
                await page.wait_for_load_state("networkidle")
                print("✓ Logged out")
        else:
            print(f"\n❌ LOGIN FAILED (as expected for wrong password)")

        result["status"] = "PASS"

    except Exception as e:
        print(f"\n❌ Error during test: {str(e)}")
        result["status"] = "ERROR"
        result["error"] = str(e)

        # Error screenshot
        try:
            error_screenshot = test_dir / f"{screenshot_prefix}_ERROR.png"
            await page.screenshot(path=str(error_screenshot), full_page=True)
            print(f"📸 Error screenshot: {error_screenshot.name}")
        except:
            pass

    return result


async def main():
    # Create output directory
    OUTPUT_DIR.mkdir(exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    test_dir = OUTPUT_DIR / f"test_test2_login_{timestamp}"
    test_dir.mkdir(exist_ok=True)

    print(f"🎯 TopSeven7 - test2 User Login Verification")
    print(f"📁 Output directory: {test_dir.absolute()}")
    print(f"👤 Test user: {TEST_EMAIL}\n")
    print(f"This test verifies:")
    print(f"  1. Login FAILS with wrong password: {WRONG_PASSWORD}")
    print(f"  2. Login SUCCEEDS with correct password: {CORRECT_PASSWORD}")

    test_results = {
        "timestamp": timestamp,
        "base_url": BASE_URL,
        "test_user": TEST_EMAIL,
        "tests": []
    }

    async with async_playwright() as p:
        # Launch browser
        print("\n🚀 Launching Chromium browser...")
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            locale="fr-FR"
        )
        page = await context.new_page()

        try:
            # Test 1: Login with WRONG password (should fail)
            result1 = await test_login(
                page=page,
                email=TEST_EMAIL,
                password=WRONG_PASSWORD,
                test_name="Login with WRONG password (should fail)",
                test_dir=test_dir,
                screenshot_prefix="test1_wrong_password"
            )
            test_results["tests"].append(result1)

            # Clear cookies between tests
            await context.clear_cookies()

            # Test 2: Login with CORRECT password (should succeed)
            result2 = await test_login(
                page=page,
                email=TEST_EMAIL,
                password=CORRECT_PASSWORD,
                test_name="Login with CORRECT password (should succeed)",
                test_dir=test_dir,
                screenshot_prefix="test2_correct_password"
            )
            test_results["tests"].append(result2)

        except Exception as e:
            print(f"\n❌ Critical error: {str(e)}")
            test_results["critical_error"] = str(e)

        finally:
            await browser.close()

    # Save results
    results_path = test_dir / "login_test_results.json"
    with open(results_path, "w", encoding="utf-8") as f:
        json.dump(test_results, f, indent=2, ensure_ascii=False)

    # Print summary
    print("\n" + "="*60)
    print("TEST SUMMARY")
    print("="*60)

    for i, test in enumerate(test_results["tests"], 1):
        print(f"\nTest {i}: {test['test_name']}")
        print(f"  Status: {test['status']}")
        print(f"  Email: {test['email']}")
        print(f"  Password: {test['password']}")
        print(f"  Login Successful: {test.get('login_successful', 'N/A')}")

        if test.get('error_indicators'):
            print(f"  Error Indicators: {', '.join(test['error_indicators'])}")

    print(f"\n📊 Full results saved: {results_path}")
    print(f"📁 All files: {test_dir.absolute()}")

    # Final verdict
    print("\n" + "="*60)
    print("VERDICT")
    print("="*60)

    test1 = test_results["tests"][0] if len(test_results["tests"]) > 0 else None
    test2 = test_results["tests"][1] if len(test_results["tests"]) > 1 else None

    if test1:
        if test1.get("login_successful"):
            print(f"⚠️  UNEXPECTED: Login with WRONG password '{WRONG_PASSWORD}' succeeded!")
            print(f"    This should have failed!")
        else:
            print(f"✓ EXPECTED: Login with wrong password '{WRONG_PASSWORD}' failed correctly")

    if test2:
        if test2.get("login_successful"):
            print(f"✓ EXPECTED: Login with correct password '{CORRECT_PASSWORD}' succeeded!")
        else:
            print(f"❌ PROBLEM: Login with correct password '{CORRECT_PASSWORD}' failed!")
            print(f"    This indicates an authentication issue!")

    print("\n" + "="*60)
    print("✅ Test suite completed!")
    print("="*60)

if __name__ == "__main__":
    asyncio.run(main())
