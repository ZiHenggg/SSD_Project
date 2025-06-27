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
        // Simulate rate limit exceeded
        $_SESSION['registration_attempts'] = 5;

        $mockRepo = $this->createMock(StudentRepository::class);

        $control = $this->getMockBuilder(StudentControl::class)
            ->setConstructorArgs([$mockRepo])
            ->onlyMethods(['sendOtpEmail'])
            ->getMock();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Too many registration attempts. Please try again later.');

        $control->registerStudentAccount(
            1234567,
            'Rate Limit',
            'limit@sit.singaporetech.edu.sg',
            'StrongPass!123'
        );
    }

    public function testOtpVerificationRateLimitExceeded()
    {
        // Simulate rate limit exceeded
        $_SESSION['otp_attempts'] = 5;
        $_SESSION['otp'] = '123456';
        $_SESSION['otp_expiry'] = time() + 600;
        $_SESSION['pending_registration'] = [
            'studentId' => 7654321,
            'studentName' => 'Rate Limit',
            'email' => 'limit@sit.singaporetech.edu.sg',
            'password' => password_hash('StrongPass!123', PASSWORD_DEFAULT)
        ];

        $mockRepo = $this->createMock(StudentRepository::class);
        $control = new StudentControl($mockRepo);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Too many OTP verification attempts. Please try again later.');

        $control->verifyOtp('123456');
    }

    public function testResendOtpRateLimitExceeded()
    {
        $_SESSION['pending_registration'] = [
            'studentId' => 1010101,
            'studentName' => 'Test User',
            'email' => 'limit@sit.singaporetech.edu.sg',
            'password' => password_hash('password', PASSWORD_DEFAULT)
        ];

        $_SESSION['resend_otp_attempts'] = 3;

        $mockRepo = $this->createMock(StudentRepository::class);
        $control = $this->getMockBuilder(StudentControl::class)
            ->setConstructorArgs([$mockRepo])
            ->onlyMethods(['sendOtpEmail'])
            ->getMock();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Too many OTP resend attempts. Please wait before trying again.');

        $control->resendOtp('limit@sit.singaporetech.edu.sg');
    }
}
