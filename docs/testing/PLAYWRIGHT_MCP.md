# Using MCP Playwright Server

This guide explains how to use the MCP (Model Context Protocol) Playwright server for testing, as an alternative to direct Playwright scripts.

## What is MCP Playwright?

MCP Playwright is a server that exposes Playwright functionality through the Model Context Protocol, allowing AI assistants like Claude to control browsers programmatically.

## Configuration

Your MCP Playwright server is already configured in `.claude/mcp_settings.json`:

```json
{
  "mcpServers": {
    "playwright": {
      "command": "npx",
      "args": [
        "-y",
        "@executeautomation/playwright-mcp-server"
      ],
      "env": {
        "PLAYWRIGHT_BROWSERS_PATH": "0"
      }
    }
  }
}
```

## Available MCP Tools

Once the MCP server is running, Claude Code can access these Playwright tools:

- `mcp__playwright_navigate` - Navigate to a URL
- `mcp__playwright_screenshot` - Take screenshots
- `mcp__playwright_click` - Click elements
- `mcp__playwright_fill` - Fill form fields
- `mcp__playwright_evaluate` - Run JavaScript in browser
- And more...

## Using MCP Playwright vs Direct Scripts

### Direct Playwright Script (Current Approach)
```bash
# Run a Node.js script with Playwright
node test-all-pages.js
```

**Pros:**
- Full control over test logic
- Can generate HTML reports
- Runs independently
- Customizable

**Cons:**
- Requires Node.js scripting
- Must write test code manually

### MCP Playwright (AI-Driven)
```
Claude can directly control the browser through MCP tools
```

**Pros:**
- No scripting needed
- Natural language instructions
- Direct AI control
- Interactive testing

**Cons:**
- Limited to available MCP tools
- May require more context/tokens
- Less suitable for automated CI/CD

## When to Use Each Approach

### Use Direct Playwright Scripts When:
1. Running automated test suites in CI/CD
2. Need comprehensive HTML reports
3. Testing multiple pages in batch
4. Want version-controlled test code
5. Need custom error detection logic

### Use MCP Playwright When:
1. Interactive debugging/exploration
2. One-off manual tests
3. Quick verification of specific features
4. Need AI to analyze page content
5. Want natural language test instructions

## Example: Testing with MCP Playwright

Instead of running a script, you can ask Claude:

```
"Using MCP Playwright, navigate to http://localhost/login,
fill in the login form with test2@topseven.fr / Passw0rd,
click submit, take a screenshot, then check if we're logged in"
```

Claude would then use MCP tools like:
- `mcp__playwright_navigate` to go to login page
- `mcp__playwright_fill` to enter credentials
- `mcp__playwright_click` to submit
- `mcp__playwright_screenshot` to capture result
- `mcp__playwright_evaluate` to check for login indicators

## Checking MCP Server Status

To verify the MCP Playwright server is available:

```bash
# Check if the MCP server package is installed
npx @executeautomation/playwright-mcp-server --version
```

If you get an error, the package may need to be installed first.

## Current Test Results

Your comprehensive test found these issues:

### ✓ Working Pages (9/20):
- Login page
- Password reset
- Introduction
- Information
- Parameters
- Team Agenda
- Register New Season
- Display

### ✗ Pages with Errors (11/20):
- **Register**: PHP Warning
- **Player Profile**: PHP Warning, Fatal Error
- **Team Management**: TOP7 - Error (Application error)
- **Pronostics**: PHP Warning, Fatal Error, Undefined function
- **Rankings**: TOP7 - Error, PHP Warning
- **Rank7**: TOP7 - Error, PHP Warning
- **Records**: TOP7 - Error
- **Statistics**: TOP7 - Error
- **Statistics Graphs**: TOP7 - Error
- **Calendar**: TOP7 - Error
- **LNR Rankings**: PHP Warning, Fatal Error, Undefined function

### Common Error Found:
SQL syntax error in multiple pages:
```sql
select * from `season` where Id=
```
The `Id` parameter is empty, causing failures.

## Recommendation

**For your use case, the direct Playwright scripts are better because:**

1. ✓ Login **IS working correctly** - The test confirmed this
2. ✓ Session persistence works - Can access protected pages
3. ✓ The errors found are **real application bugs**, not test issues
4. ✓ You get detailed HTML reports with screenshots
5. ✓ Can run comprehensive tests on all pages automatically

The errors found (like the SQL `Id=` bug) need to be fixed in the application code, not in the test approach.

## Next Steps

### 1. Review the HTML Report
```bash
start test-screenshots/report.html  # Windows
xdg-open test-screenshots/report.html  # Linux
```

### 2. Fix the SQL Errors
The most common issue is:
```php
// Bug: Id parameter is empty
$query = "select * from `season` where Id=$id";
```

Check where `$id` or similar parameters are not being set properly.

### 3. Fix Page-Specific Errors
Check the log files for details:
```bash
docker exec test-web-1 tail -100 /tmp/log_$(date +%Y%m%d).txt
```

### 4. Re-run Tests After Fixes
```bash
node test-all-pages.js
```

## Conclusion

Your test infrastructure is working perfectly:
- ✓ Login works
- ✓ Session works
- ✓ Tests are finding real bugs

The 11 failing pages have actual application errors that need fixing, which is exactly what the tests should detect!

Keep using the direct Playwright scripts for comprehensive testing, and use MCP Playwright when you need quick interactive checks.
