#!/bin/bash

# Top7 Test Runner
# Runs all tests: PHPUnit and Playwright

echo "========================================"
echo "         TOP7 TEST RUNNER"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Track results
PHPUNIT_RESULT=0
PLAYWRIGHT_LOGIN_RESULT=0
PLAYWRIGHT_CLASSEMENT_RESULT=0

# Run PHPUnit tests
echo "----------------------------------------"
echo "Running PHPUnit Tests..."
echo "----------------------------------------"

cd /home/user/top7

if [ -f "vendor/bin/phpunit" ]; then
    ./vendor/bin/phpunit --testdox
    PHPUNIT_RESULT=$?
else
    echo -e "${YELLOW}Warning: PHPUnit not installed. Run 'composer install' first.${NC}"
    PHPUNIT_RESULT=1
fi

echo ""

# Check if Docker is running for Playwright tests
if ! docker ps | grep -q "top7"; then
    echo -e "${YELLOW}Warning: Docker container 'top7' is not running.${NC}"
    echo "Skipping Playwright tests. Start the container with 'docker-compose up -d'"
else
    # Run Playwright login tests
    echo "----------------------------------------"
    echo "Running Playwright Login Tests..."
    echo "----------------------------------------"

    cd /home/user/top7/tests/playwright

    if command -v node &> /dev/null; then
        # Install Playwright if needed
        npx -y playwright install chromium 2>/dev/null

        node test-login-complete.js
        PLAYWRIGHT_LOGIN_RESULT=$?
    else
        echo -e "${YELLOW}Warning: Node.js not installed.${NC}"
        PLAYWRIGHT_LOGIN_RESULT=1
    fi

    echo ""

    # Run Playwright classement tests
    echo "----------------------------------------"
    echo "Running Playwright Classement Tests..."
    echo "----------------------------------------"

    if command -v node &> /dev/null; then
        node test-classement.js
        PLAYWRIGHT_CLASSEMENT_RESULT=$?
    fi
fi

echo ""

# Summary
echo "========================================"
echo "           TEST SUMMARY"
echo "========================================"

if [ $PHPUNIT_RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ PHPUnit Tests: PASSED${NC}"
else
    echo -e "${RED}✗ PHPUnit Tests: FAILED${NC}"
fi

if [ $PLAYWRIGHT_LOGIN_RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ Playwright Login Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Playwright Login Tests: FAILED${NC}"
fi

if [ $PLAYWRIGHT_CLASSEMENT_RESULT -eq 0 ]; then
    echo -e "${GREEN}✓ Playwright Classement Tests: PASSED${NC}"
else
    echo -e "${RED}✗ Playwright Classement Tests: FAILED${NC}"
fi

echo "========================================"

# Exit with error if any tests failed
if [ $PHPUNIT_RESULT -ne 0 ] || [ $PLAYWRIGHT_LOGIN_RESULT -ne 0 ] || [ $PLAYWRIGHT_CLASSEMENT_RESULT -ne 0 ]; then
    exit 1
fi

exit 0
