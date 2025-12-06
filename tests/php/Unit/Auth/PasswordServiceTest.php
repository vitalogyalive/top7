<?php

declare(strict_types=1);

namespace Top7\Tests\Unit\Auth;

use PHPUnit\Framework\TestCase;
use Top7\Auth\PasswordService;

/**
 * Unit tests for PasswordService
 *
 * Tests password hashing, verification, and legacy MD5 support.
 */
class PasswordServiceTest extends TestCase
{
    private PasswordService $passwordService;

    protected function setUp(): void
    {
        $this->passwordService = new PasswordService();
    }

    /**
     * Test that hash() creates a valid Argon2ID hash
     */
    public function testHashCreatesArgon2idHash(): void
    {
        $password = 'testPassword123!';
        $hash = $this->passwordService->hash($password);

        // Argon2ID hashes start with $argon2id$
        $this->assertStringStartsWith('$argon2id$', $hash);
    }

    /**
     * Test that hash() produces different hashes for same password (salt)
     */
    public function testHashProducesDifferentHashesForSamePassword(): void
    {
        $password = 'testPassword123!';

        $hash1 = $this->passwordService->hash($password);
        $hash2 = $this->passwordService->hash($password);

        $this->assertNotEquals($hash1, $hash2);
    }

    /**
     * Test that verify() returns true for correct password
     */
    public function testVerifyReturnsTrueForCorrectPassword(): void
    {
        $password = 'testPassword123!';
        $hash = $this->passwordService->hash($password);

        $this->assertTrue($this->passwordService->verify($password, $hash));
    }

    /**
     * Test that verify() returns false for incorrect password
     */
    public function testVerifyReturnsFalseForIncorrectPassword(): void
    {
        $password = 'testPassword123!';
        $wrongPassword = 'wrongPassword456!';
        $hash = $this->passwordService->hash($password);

        $this->assertFalse($this->passwordService->verify($wrongPassword, $hash));
    }

    /**
     * Test that verify() returns false for empty password
     */
    public function testVerifyReturnsFalseForEmptyPassword(): void
    {
        $password = 'testPassword123!';
        $hash = $this->passwordService->hash($password);

        $this->assertFalse($this->passwordService->verify('', $hash));
    }

    /**
     * Test that needsRehash() returns false for fresh Argon2ID hash
     */
    public function testNeedsRehashReturnsFalseForFreshHash(): void
    {
        $password = 'testPassword123!';
        $hash = $this->passwordService->hash($password);

        $this->assertFalse($this->passwordService->needsRehash($hash));
    }

    /**
     * Test that needsRehash() returns true for MD5 hash
     */
    public function testNeedsRehashReturnsTrueForMd5Hash(): void
    {
        $md5Hash = md5('testPassword');

        $this->assertTrue($this->passwordService->needsRehash($md5Hash));
    }

    /**
     * Test that needsRehash() returns true for bcrypt hash
     */
    public function testNeedsRehashReturnsTrueForBcryptHash(): void
    {
        $bcryptHash = password_hash('testPassword', PASSWORD_BCRYPT);

        $this->assertTrue($this->passwordService->needsRehash($bcryptHash));
    }

    /**
     * Test legacy MD5 verification with correct password
     */
    public function testVerifyLegacyMd5WithCorrectPassword(): void
    {
        $password = 'legacyPassword';
        $md5Hash = md5($password);

        $this->assertTrue($this->passwordService->verifyLegacyMd5($password, $md5Hash));
    }

    /**
     * Test legacy MD5 verification with incorrect password
     */
    public function testVerifyLegacyMd5WithIncorrectPassword(): void
    {
        $password = 'legacyPassword';
        $wrongPassword = 'wrongPassword';
        $md5Hash = md5($password);

        $this->assertFalse($this->passwordService->verifyLegacyMd5($wrongPassword, $md5Hash));
    }

    /**
     * Test that hash handles special characters
     */
    public function testHashHandlesSpecialCharacters(): void
    {
        $password = 'pässwörd!@#$%^&*()_+{}|:"<>?`~éèü';
        $hash = $this->passwordService->hash($password);

        $this->assertTrue($this->passwordService->verify($password, $hash));
    }

    /**
     * Test that hash handles very long passwords
     */
    public function testHashHandlesLongPasswords(): void
    {
        $password = str_repeat('a', 1000);
        $hash = $this->passwordService->hash($password);

        $this->assertTrue($this->passwordService->verify($password, $hash));
    }

    /**
     * Test that hash handles unicode characters
     */
    public function testHashHandlesUnicodeCharacters(): void
    {
        $password = '密码测试🔐';
        $hash = $this->passwordService->hash($password);

        $this->assertTrue($this->passwordService->verify($password, $hash));
    }
}
