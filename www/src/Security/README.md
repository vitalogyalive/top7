# Security Enhancements - Phase 1

## CSRF Protection Implementation

### Overview
CSRF (Cross-Site Request Forgery) protection has been added to prevent unauthorized actions on behalf of authenticated users.

### Implementation Details

**Class**: `Top7\Security\CsrfToken`
**Location**: `src/Security/CsrfToken.php`

### Features
- Multi-token support (allows multiple tabs/windows)
- Token expiration (2 hours)
- One-time use tokens
- Automatic session management

### Usage

#### In Forms
Add CSRF token field to any form:

```php
echo "<form method='POST' action='handler'>";
echo \Top7\Security\CsrfToken::field();
// ... rest of form fields
echo "</form>";
```

#### In Form Handlers
Validate CSRF token before processing:

```php
// Method 1: Manual validation with custom handling
if (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token'])) {
    error_log('CSRF token validation failed');
    header('Location: form_page');
    exit;
}

// Method 2: Auto-die on failure
\Top7\Security\CsrfToken::validateOrDie($_POST['csrf_token'] ?? null);
```

### Protected Forms (Phase 1 Complete)

#### ✅ Authentication Forms
- [x] Login form (`put_login_form` → `login.php`)
- [x] Password reset request (`put_password_form` → `password.php`)
- [x] Password reset form (`put_new_password_form` → `update_password.php`)

#### ✅ Registration Forms
- [x] New team registration (`put_register_form` → `register.php`)

### Forms Requiring Protection (Phase 2)

#### Game Functionality
- [ ] Prono updates (`update_prono.php`, `update_prono_player.php`, `display_update_prono.php`)
- [ ] Match updates (`update_match.php`, `display_update_match.php`)
- [ ] Team management (`team.php`)
- [ ] Player settings (`player.php`, `params.php`)

#### Admin Functions
- [ ] Day updates (`update_day.php`)
- [ ] Forum updates (`update_forum.php`)

#### Other
- [ ] Season registration (`register_new_season.php`)
- [ ] Display/navigation forms (`display.php`, `stats.php`, `rank.php`, `rank7.php`, `records.php`)

### How to Add CSRF Protection to a New Form

1. **Add token to form**:
   ```php
   function put_my_form() {
       echo "<form method='POST'>";
       echo \Top7\Security\CsrfToken::field(); // Add this line
       // ... form fields
   }
   ```

2. **Validate in handler**:
   ```php
   include("common.inc");

   // Add validation at the beginning
   if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
       (!isset($_POST['csrf_token']) || !\Top7\Security\CsrfToken::validate($_POST['csrf_token']))) {
       error_log('CSRF token validation failed');
       header('Location: form_page');
       exit;
   }

   // ... rest of processing logic
   ```

### Testing CSRF Protection

#### Test 1: Valid Token
1. Load form (token generated)
2. Submit form
3. Should process successfully

#### Test 2: Missing Token
```bash
curl -X POST http://localhost/login -d "login=test&password=test"
# Should redirect to index without logging in
```

#### Test 3: Expired Token
1. Load form
2. Wait 3+ hours
3. Submit form
4. Should reject (token expired)

#### Test 4: Replay Attack
1. Capture valid POST request
2. Submit it twice
3. Second submission should fail (one-time use)

### Security Benefits

| Attack Type | Protection |
|-------------|------------|
| CSRF | ✅ Token validation |
| Replay attacks | ✅ One-time use |
| Token theft | ⚠️ Partial (session-based) |
| Multi-tab usage | ✅ Up to 5 concurrent tokens |

### Migration Notes

**Old token system**: The codebase had a partial token implementation in `index.php` that generated tokens but didn't validate them properly.

**New system**: Comprehensive token generation, validation, and expiration with multi-token support.

### Next Steps

1. Add CSRF protection to remaining forms (listed above)
2. Consider adding SameSite cookie attribute
3. Implement Content Security Policy headers
4. Add rate limiting for form submissions

### Related

- Password hashing: `src/Auth/PasswordService.php`
- Session management: `common.inc`
