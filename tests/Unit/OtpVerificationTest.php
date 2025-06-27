<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\Entity\Student;

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

        $_SESSION['otp'] = '123456';
        $_SESSION['otp_expiry'] = time() + 300;
        $_SESSION['pending_registration'] = [
            'studentId' => 1234567,
            'studentName' => 'Jane Doe',
            'email' => 'test@sit.singaporetech.edu.sg',
            'password' => password_hash('securePassword!', PASSWORD_DEFAULT)
        ];

        $control = new StudentControl($mockRepo);
        $result = $control->verifyOtp('123456');

        $this->assertTrue($result['success']);
        $this->assertEquals('Registration complete!', $result['message']);
        $this->assertArrayNotHasKey('otp', $_SESSION);
    }

    public function testVerifyOtpFailsIfExpired()
    {
        $_SESSION['otp'] = '123456';
        $_SESSION['otp_expiry'] = time() - 1;
        $_SESSION['pending_registration'] = [
            'email' => 'test@sit.singaporetech.edu.sg'
        ];

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $result = $control->verifyOtp('123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('OTP expired.', $result['message']);
    }

    public function testVerifyOtpFailsIfIncorrect()
    {
        $_SESSION['otp'] = '123456';
        $_SESSION['otp_expiry'] = time() + 300;
        $_SESSION['pending_registration'] = [
            'email' => 'test@sit.singaporetech.edu.sg'
        ];

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $result = $control->verifyOtp('000000');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid OTP.', $result['message']);
    }
}
