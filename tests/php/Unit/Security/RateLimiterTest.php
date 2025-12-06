<?php

declare(strict_types=1);

namespace Top7\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Top7\Security\RateLimiter;

/**
 * Unit tests for RateLimiter
 *
 * Tests rate limiting functionality for brute force protection.
 */
class RateLimiterTest extends TestCase
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
     * Test that check() returns allowed for fresh identifier
     */
    public function testCheckReturnsAllowedForFreshIdentifier(): void
    {
        $result = RateLimiter::check('login', '192.168.1.1');

        $this->assertTrue($result['allowed']);
        $this->assertEquals(5, $result['remaining']);
        $this->assertNull($result['retry_after']);
    }

    /**
     * Test that recordFailure() decrements remaining attempts
     */
    public function testRecordFailureDecrementsRemainingAttempts(): void
    {
        $identifier = 'test_user_1';

        RateLimiter::recordFailure('login', $identifier);
        $result = RateLimiter::check('login', $identifier);

        $this->assertTrue($result['allowed']);
        $this->assertEquals(4, $result['remaining']);
    }

    /**
     * Test that multiple failures lead to lockout
     */
    public function testMultipleFailuresLeadToLockout(): void
    {
        $identifier = 'test_user_2';

        // Record 5 failures (default max attempts)
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::recordFailure('login', $identifier);
        }

        $result = RateLimiter::check('login', $identifier);

        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);
        $this->assertNotNull($result['retry_after']);
    }

    /**
     * Test that recordSuccess() clears rate limiting
     */
    public function testRecordSuccessClearsRateLimiting(): void
    {
        $identifier = 'test_user_3';

        // Record some failures
        RateLimiter::recordFailure('login', $identifier);
        RateLimiter::recordFailure('login', $identifier);

        // Record success
        RateLimiter::recordSuccess('login', $identifier);

        // Should be fresh again
        $result = RateLimiter::check('login', $identifier);
        $this->assertTrue($result['allowed']);
        $this->assertEquals(5, $result['remaining']);
    }

    /**
     * Test that clear() removes rate limiting data
     */
    public function testClearRemovesRateLimitingData(): void
    {
        $identifier = 'test_user_4';

        // Record failures
        RateLimiter::recordFailure('login', $identifier);
        RateLimiter::recordFailure('login', $identifier);

        // Clear
        RateLimiter::clear('login', $identifier);

        // Should be fresh
        $result = RateLimiter::check('login', $identifier);
        $this->assertEquals(5, $result['remaining']);
    }

    /**
     * Test that different actions are tracked separately
     */
    public function testDifferentActionsTrackedSeparately(): void
    {
        $identifier = 'test_user_5';

        // Fail login action
        RateLimiter::recordFailure('login', $identifier);

        // Password reset should be separate
        $loginResult = RateLimiter::check('login', $identifier);
        $resetResult = RateLimiter::check('password_reset', $identifier);

        $this->assertEquals(4, $loginResult['remaining']);
        $this->assertEquals(5, $resetResult['remaining']);
    }

    /**
     * Test that different identifiers are tracked separately
     */
    public function testDifferentIdentifiersTrackedSeparately(): void
    {
        RateLimiter::recordFailure('login', 'user_a');

        $userAResult = RateLimiter::check('login', 'user_a');
        $userBResult = RateLimiter::check('login', 'user_b');

        $this->assertEquals(4, $userAResult['remaining']);
        $this->assertEquals(5, $userBResult['remaining']);
    }

    /**
     * Test isLockedOut() returns correct status
     */
    public function testIsLockedOutReturnsCorrectStatus(): void
    {
        $identifier = 'test_user_6';

        $this->assertFalse(RateLimiter::isLockedOut('login', $identifier));

        // Lock out the user
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::recordFailure('login', $identifier);
        }

        $this->assertTrue(RateLimiter::isLockedOut('login', $identifier));
    }

    /**
     * Test getLockoutRemaining() returns correct time
     */
    public function testGetLockoutRemainingReturnsCorrectTime(): void
    {
        $identifier = 'test_user_7';

        // Not locked - should return null
        $this->assertNull(RateLimiter::getLockoutRemaining('login', $identifier));

        // Lock out the user (15 min = 900 seconds)
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::recordFailure('login', $identifier, 5, 900);
        }

        $remaining = RateLimiter::getLockoutRemaining('login', $identifier);
        $this->assertNotNull($remaining);
        $this->assertGreaterThan(0, $remaining);
        $this->assertLessThanOrEqual(900, $remaining);
    }

    /**
     * Test custom max attempts and lockout time
     */
    public function testCustomMaxAttemptsAndLockoutTime(): void
    {
        $identifier = 'test_user_8';

        // Use custom settings: 3 attempts, 60 second lockout
        for ($i = 0; $i < 3; $i++) {
            RateLimiter::recordFailure('login', $identifier, 3, 60);
        }

        $result = RateLimiter::check('login', $identifier, 3, 60);

        $this->assertFalse($result['allowed']);
        $this->assertLessThanOrEqual(60, $result['retry_after']);
    }

    /**
     * Test formatRemainingTime() for seconds
     */
    public function testFormatRemainingTimeForSeconds(): void
    {
        $this->assertEquals('30 secondes', RateLimiter::formatRemainingTime(30));
        $this->assertEquals('1 seconde', RateLimiter::formatRemainingTime(1));
    }

    /**
     * Test formatRemainingTime() for minutes
     */
    public function testFormatRemainingTimeForMinutes(): void
    {
        $this->assertEquals('2 minutes', RateLimiter::formatRemainingTime(90));
        $this->assertEquals('1 minute', RateLimiter::formatRemainingTime(60));
        $this->assertEquals('15 minutes', RateLimiter::formatRemainingTime(900));
    }

    /**
     * Test getClientIp() returns valid IP
     */
    public function testGetClientIpReturnsValidIp(): void
    {
        // In test environment, this will return the mock value
        $ip = RateLimiter::getClientIp();

        // Should return some kind of IP address
        $this->assertNotEmpty($ip);
    }
}
