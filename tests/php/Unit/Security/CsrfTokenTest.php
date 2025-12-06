<?php

declare(strict_types=1);

namespace Top7\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Top7\Security\CsrfToken;

/**
 * Unit tests for CsrfToken
 *
 * Tests CSRF token generation, validation, and security features.
 */
class CsrfTokenTest extends TestCase
{
    protected function setUp(): void
    {
        // Start a fresh session for each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            @session_destroy();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            @session_destroy();
        }
        $_SESSION = [];
    }

    /**
     * Test that generate() creates a valid hex string token
     */
    public function testGenerateCreatesValidHexToken(): void
    {
        $token = CsrfToken::generate();

        // Token should be 64 characters (32 bytes as hex)
        $this->assertEquals(64, strlen($token));
        // Token should be a valid hex string
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    /**
     * Test that generate() creates different tokens each time
     */
    public function testGenerateCreatesDifferentTokens(): void
    {
        $token1 = CsrfToken::generate();
        $token2 = CsrfToken::generate();

        $this->assertNotEquals($token1, $token2);
    }

    /**
     * Test that validate() returns true for valid token
     */
    public function testValidateReturnsTrueForValidToken(): void
    {
        $token = CsrfToken::generate();

        $this->assertTrue(CsrfToken::validate($token));
    }

    /**
     * Test that validate() returns false for invalid token
     */
    public function testValidateReturnsFalseForInvalidToken(): void
    {
        CsrfToken::generate(); // Generate to initialize session

        $this->assertFalse(CsrfToken::validate('invalid_token'));
    }

    /**
     * Test that validate() returns false for empty token
     */
    public function testValidateReturnsFalseForEmptyToken(): void
    {
        CsrfToken::generate();

        $this->assertFalse(CsrfToken::validate(''));
    }

    /**
     * Test that token is one-time use (removed after validation)
     */
    public function testTokenIsOneTimeUse(): void
    {
        $token = CsrfToken::generate();

        // First validation should succeed
        $this->assertTrue(CsrfToken::validate($token));

        // Second validation with same token should fail
        $this->assertFalse(CsrfToken::validate($token));
    }

    /**
     * Test that field() returns valid HTML hidden input
     */
    public function testFieldReturnsValidHtmlInput(): void
    {
        $field = CsrfToken::field();

        // Should be a hidden input
        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('value="', $field);
    }

    /**
     * Test that multiple tokens can be stored (multi-tab support)
     */
    public function testMultipleTokensAreStored(): void
    {
        $tokens = [];
        for ($i = 0; $i < 5; $i++) {
            $tokens[] = CsrfToken::generate();
        }

        // All tokens should be valid
        foreach ($tokens as $token) {
            $this->assertTrue(CsrfToken::validate($token));
        }
    }

    /**
     * Test that old tokens are removed when limit exceeded
     */
    public function testOldTokensRemovedWhenLimitExceeded(): void
    {
        // Generate more than 5 tokens (the limit)
        $firstToken = CsrfToken::generate();
        for ($i = 0; $i < 5; $i++) {
            CsrfToken::generate();
        }

        // First token should be removed
        $this->assertFalse(CsrfToken::validate($firstToken));
    }

    /**
     * Test validateOrDie() with valid token
     */
    public function testValidateOrDieWithValidToken(): void
    {
        $token = CsrfToken::generate();

        // This should not throw or die
        CsrfToken::validateOrDie($token);
        $this->assertTrue(true); // If we get here, test passed
    }

    /**
     * Test that tokens are stored in session
     */
    public function testTokensStoredInSession(): void
    {
        $token = CsrfToken::generate();

        $this->assertArrayHasKey('csrf_tokens', $_SESSION);
        $this->assertArrayHasKey($token, $_SESSION['csrf_tokens']);
    }
}
