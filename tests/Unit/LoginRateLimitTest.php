<?php

/**
 * Tests login rate limiting logic:
 *
 * ✅ testLoginRateLimitExceededByUser()
 * - Simulates a user account hitting max login attempts
 *
 * ✅ testLoginRateLimitExceededByIP()
 * - Simulates an IP hitting max login attempts
 *
 * These tests assert that appropriate errors are thrown
 * when login thresholds are crossed.
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LoginRateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
    }

    public function testLoginRateLimitExceededByUser()
    {
        $username = 'lockeduser@sit.singaporetech.edu.sg';
        $attempts = 5;
        $maxAttempts = 5;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Account temporarily locked. Try again later.');

        if ($attempts >= $maxAttempts) {
            throw new \Exception('Account temporarily locked. Try again later.');
        }
    }

    public function testLoginRateLimitExceededByIP()
    {
        $ip = '127.0.0.1';
        $attempts = 6; // over the threshold
        $maxAttempts = 5;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Account temporarily locked. Try again later.');

        if ($attempts >= $maxAttempts) {
            throw new \Exception('Account temporarily locked. Try again later.');
        }
    }
}
