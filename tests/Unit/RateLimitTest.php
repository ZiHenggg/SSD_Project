<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;

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
        $_SESSION['registration_attempts'] = [
            time() - 5, time() - 4, time() - 3, time() - 2, time() - 1
        ];

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Too many registration attempts. Please try again later.");
        $control->registerStudentAccount(1234567, 'John Doe', 'john@sit.singaporetech.edu.sg', 'password');
    }

    public function testOtpVerificationRateLimitExceeded()
    {
        $_SESSION['otp_attempts'] = [time() - 3, time() - 2, time() - 1];

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Too many OTP attempts. Please try again later.");
        $control->verifyOtp('123456');
    }

    public function testResendOtpRateLimitExceeded()
    {
        $_SESSION['resend_attempts'] = [
            time() - 5, time() - 4, time() - 3
        ];
        $_SESSION['pending_registration'] = ['email' => 'jane@sit.singaporetech.edu.sg'];

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Too many OTP resend attempts. Please wait before trying again.");
        $control->resendOtp('jane@sit.singaporetech.edu.sg');
    }
}
