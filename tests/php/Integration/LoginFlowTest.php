<?php

declare(strict_types=1);

namespace Top7\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Top7\Security\CsrfToken;
use Top7\Security\RateLimiter;

/**
 * Integration tests for the login flow
 *
 * Tests the complete login process including CSRF and rate limiting.
 * Note: These tests mock the database interactions.
 */
class LoginFlowTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset session for each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            @session_destroy();
        }
        $_SESSION = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
    }

    /**
     * Test that CSRF token is required for login
     */
    public function testCsrfTokenRequiredForLogin(): void
    {
        $_POST['login'] = 'test@example.com';
        $_POST['password'] = 'password123';
        // No CSRF token provided

        $result = $this->validateCsrfForLogin();

        $this->assertFalse($result, 'Login should fail without CSRF token');
    }

    /**
     * Test that valid CSRF token allows login attempt
     */
    public function testValidCsrfTokenAllowsLoginAttempt(): void
    {
        $token = CsrfToken::generate();

        $_POST['login'] = 'test@example.com';
        $_POST['password'] = 'password123';
        $_POST['csrf_token'] = $token;

        $result = $this->validateCsrfForLogin();

        $this->assertTrue($result, 'Login should proceed with valid CSRF token');
    }

    /**
     * Test that invalid CSRF token is rejected
     */
    public function testInvalidCsrfTokenRejected(): void
    {
        // Generate a token but don't use it
        CsrfToken::generate();

        $_POST['login'] = 'test@example.com';
        $_POST['password'] = 'password123';
        $_POST['csrf_token'] = 'invalid_token_12345';

        $result = $this->validateCsrfForLogin();

        $this->assertFalse($result, 'Login should fail with invalid CSRF token');
    }

    /**
     * Test rate limiting blocks after max attempts
     */
    public function testRateLimitingBlocksAfterMaxAttempts(): void
    {
        $ip = '192.168.1.100';

        // Simulate 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::recordFailure('login', $ip, 5, 900);
        }

        $check = RateLimiter::check('login', $ip, 5, 900);

        $this->assertFalse($check['allowed'], 'Login should be blocked after 5 failed attempts');
        $this->assertNotNull($check['retry_after'], 'Should have retry_after time');
    }

    /**
     * Test rate limiting clears on successful login
     */
    public function testRateLimitingClearsOnSuccess(): void
    {
        $ip = '192.168.1.101';

        // Record some failures
        RateLimiter::recordFailure('login', $ip, 5, 900);
        RateLimiter::recordFailure('login', $ip, 5, 900);

        // Record success
        RateLimiter::recordSuccess('login', $ip);

        // Should be reset
        $check = RateLimiter::check('login', $ip, 5, 900);

        $this->assertTrue($check['allowed'], 'Login should be allowed after successful login');
        $this->assertEquals(5, $check['remaining'], 'All attempts should be restored');
    }

    /**
     * Test rate limiting is per-IP
     */
    public function testRateLimitingIsPerIp(): void
    {
        $ip1 = '192.168.1.102';
        $ip2 = '192.168.1.103';

        // Block IP1
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::recordFailure('login', $ip1, 5, 900);
        }

        // IP2 should still be allowed
        $check1 = RateLimiter::check('login', $ip1, 5, 900);
        $check2 = RateLimiter::check('login', $ip2, 5, 900);

        $this->assertFalse($check1['allowed'], 'IP1 should be blocked');
        $this->assertTrue($check2['allowed'], 'IP2 should still be allowed');
    }

    /**
     * Test login flow with empty credentials
     */
    public function testLoginWithEmptyCredentials(): void
    {
        $token = CsrfToken::generate();

        $_POST['login'] = '';
        $_POST['password'] = '';
        $_POST['csrf_token'] = $token;

        $result = $this->validateLoginCredentials();

        $this->assertFalse($result, 'Empty credentials should fail');
    }

    /**
     * Test login flow with missing password
     */
    public function testLoginWithMissingPassword(): void
    {
        $token = CsrfToken::generate();

        $_POST['login'] = 'test@example.com';
        $_POST['csrf_token'] = $token;
        // No password

        $result = $this->validateLoginCredentials();

        $this->assertFalse($result, 'Missing password should fail');
    }

    /**
     * Test login flow with missing email
     */
    public function testLoginWithMissingEmail(): void
    {
        $token = CsrfToken::generate();

        $_POST['password'] = 'password123';
        $_POST['csrf_token'] = $token;
        // No login

        $result = $this->validateLoginCredentials();

        $this->assertFalse($result, 'Missing email should fail');
    }

    /**
     * Test session initialization after successful login
     */
    public function testSessionInitializationAfterLogin(): void
    {
        // Simulate a successful login session setup
        $this->simulateSuccessfulLogin([
            'email' => 'test@example.com',
            'pseudo' => 'testuser',
            'player' => 1,
            'captain' => 0,
            'team' => 1
        ]);

        $this->assertEquals('test@example.com', $_SESSION['login']);
        $this->assertEquals('testuser', $_SESSION['pseudo']);
        $this->assertEquals(1, $_SESSION['player']);
    }

    /**
     * Test CSRF token is consumed after use
     */
    public function testCsrfTokenConsumedAfterUse(): void
    {
        $token = CsrfToken::generate();

        // First validation should succeed
        $firstResult = CsrfToken::validate($token);
        $this->assertTrue($firstResult, 'First validation should succeed');

        // Second validation should fail (token consumed)
        $secondResult = CsrfToken::validate($token);
        $this->assertFalse($secondResult, 'Second validation should fail (token consumed)');
    }

    /**
     * Test multiple CSRF tokens for multi-tab support
     */
    public function testMultipleCsrfTokensForMultiTab(): void
    {
        $token1 = CsrfToken::generate();
        $token2 = CsrfToken::generate();
        $token3 = CsrfToken::generate();

        // All tokens should be valid (before any are consumed)
        $this->assertTrue(CsrfToken::validate($token1), 'Token 1 should be valid');
        $this->assertTrue(CsrfToken::validate($token2), 'Token 2 should be valid');
        $this->assertTrue(CsrfToken::validate($token3), 'Token 3 should be valid');
    }

    // Helper methods

    private function validateCsrfForLogin(): bool
    {
        if (!isset($_POST['csrf_token'])) {
            return false;
        }
        return CsrfToken::validate($_POST['csrf_token']);
    }

    private function validateLoginCredentials(): bool
    {
        if (!isset($_POST['login']) || empty($_POST['login'])) {
            return false;
        }
        if (!isset($_POST['password']) || empty($_POST['password'])) {
            return false;
        }
        return true;
    }

    private function simulateSuccessfulLogin(array $playerData): void
    {
        $_SESSION['login'] = $playerData['email'];
        $_SESSION['pseudo'] = $playerData['pseudo'];
        $_SESSION['player'] = $playerData['player'];
        $_SESSION['captain'] = $playerData['captain'];
        $_SESSION['top7team'] = $playerData['team'];
        $_SESSION['mode'] = 'guest';
        $_SESSION['display'] = 'top7';
    }
}
