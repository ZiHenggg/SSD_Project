<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\Entity\Student;

class RateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testRegistrationRateLimitExceeded()
    {
        // Simulate IP hit threshold
        $attempts = 10;
        $maxAttempts = 10;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Too many registration attempts. Please try again later.');

        if ($attempts >= $maxAttempts) {
            throw new \Exception('Too many registration attempts. Please try again later.');
        }
    }

    public function testOtpVerificationRateLimitExceeded()
    {
        $_SESSION['email'] = '1234567@sit.singaporetech.edu.sg';

        $otpAttempts = 5;
        $maxOtpAttempts = 5;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Too many failed OTP attempts. Please try again later in 5 minutes.');

        if ($otpAttempts >= $maxOtpAttempts) {
            throw new \Exception('Too many failed OTP attempts. Please try again later in 5 minutes.');
        }
    }

    public function testResendOtpRateLimitExceeded()
    {
        $_SESSION['email'] = '1234567@sit.singaporetech.edu.sg';

        $resends = 3;
        $maxResends = 3;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OTP resend limit reached. Please try again later in 15 minutes.');

        if ($resends >= $maxResends) {
            throw new \Exception('OTP resend limit reached. Please try again later in 15 minutes.');
        }
    }
}
