<?php

/**
 * Tests OTP verification logic via StudentControl::verifyOtp():
 *
 * ✅ testVerifyOtpSuccess()
 * - Accepts correct OTP and verifies student email
 * - Creates student account and clears session
 *
 * ✅ testVerifyOtpFailsIfExpired()
 * - Fails if OTP has expired
 *
 * ✅ testVerifyOtpFailsIfIncorrect()
 * - Fails if provided OTP does not match session
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\Entity\Student;
use App\SessionManager;

class OtpVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
    }

    public function testVerifyOtpSuccess()
    {
        $mockRepo = $this->createMock(StudentRepository::class);

        $mockRepo->expects($this->once())
            ->method('createStudentAccount')
            ->with($this->isInstanceOf(Student::class));

        $mockRepo->expects($this->once())
            ->method('verifyStudentEmail')
            ->with('test@sit.singaporetech.edu.sg');

        SessionManager::setRegisterOTP('123456', time() + 300);  // ✅ CORRECTED
        SessionManager::setRegistration([
            'studentId' => 1234567,
            'studentName' => 'Jane Doe',
            'email' => 'test@sit.singaporetech.edu.sg',
            'password' => password_hash('securePassword!', PASSWORD_DEFAULT)
        ]);

        $control = new StudentControl($mockRepo);
        $result = $control->verifyOtp('123456');

        $this->assertTrue($result['success']);
        $this->assertEquals('Registration complete!', $result['message']);
        $this->assertNull(SessionManager::getRegisterOTP());  // ✅ CORRECTED
        $this->assertNull(SessionManager::getRegistration());
    }

    public function testVerifyOtpFailsIfExpired()
    {
        SessionManager::setRegisterOTP('123456', time() - 1);  // ✅ CORRECTED
        SessionManager::setRegistration([
            'email' => 'test@sit.singaporetech.edu.sg'
        ]);

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $result = $control->verifyOtp('123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('OTP expired.', $result['message']);
    }

    public function testVerifyOtpFailsIfIncorrect()
    {
        SessionManager::setRegisterOTP('123456', time() + 300);  // ✅ CORRECTED
        SessionManager::setRegistration([
            'email' => 'test@sit.singaporetech.edu.sg'
        ]);

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $result = $control->verifyOtp('000000');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid OTP.', $result['message']);
    }
}
