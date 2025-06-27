<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\Entity\Student;

class OtpFlowTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testValidOtpVerifiesAccountAndCreatesStudent()
    {
        $_SESSION['otp'] = '654321';
        $_SESSION['otp_expiry'] = time() + 600;
        $_SESSION['pending_registration'] = [
            'studentId' => 7654321,
            'studentName' => 'Alice Tan',
            'email' => '7654321@sit.singaporetech.edu.sg',
            'password' => password_hash('testpass123', PASSWORD_DEFAULT)
        ];

        $mockRepo = $this->createMock(StudentRepository::class);
        $mockRepo->expects($this->once())->method('createStudentAccount');
        $mockRepo->expects($this->once())->method('verifyStudentEmail')
                 ->with('7654321@sit.singaporetech.edu.sg');

        $control = new StudentControl($mockRepo);
        $result = $control->verifyOtp('654321');

        $this->assertTrue($result['success']);
        $this->assertEquals('Registration complete!', $result['message']);
        $this->assertArrayNotHasKey('otp', $_SESSION);
        $this->assertArrayNotHasKey('pending_registration', $_SESSION);
    }

    public function testExpiredOtpFailsVerification()
    {
        $_SESSION['otp'] = '123456';
        $_SESSION['otp_expiry'] = time() - 1; // already expired
        $_SESSION['pending_registration'] = [];

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $result = $control->verifyOtp('123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('OTP expired.', $result['message']);
    }

    public function testInvalidOtpFailsVerification()
    {
        $_SESSION['otp'] = '111111';
        $_SESSION['otp_expiry'] = time() + 600;
        $_SESSION['pending_registration'] = [];

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $result = $control->verifyOtp('999999');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid OTP.', $result['message']);
    }

    public function testResendOtpGeneratesNewCode()
    {
        $_SESSION['pending_registration'] = [
            'studentId' => 7654321,
            'studentName' => 'Alice Tan',
            'email' => '7654321@sit.singaporetech.edu.sg',
            'password' => 'hashedpass'
        ];

        $mockRepo = $this->createMock(StudentRepository::class);

        $mockControl = $this->getMockBuilder(StudentControl::class)
            ->setConstructorArgs([$mockRepo])
            ->onlyMethods(['sendOtpEmail'])
            ->getMock();

        $mockControl->expects($this->once())
            ->method('sendOtpEmail')
            ->with(
                '7654321@sit.singaporetech.edu.sg',
                $this->callback(function ($otp) {
                    return (bool) preg_match('/^\d{6}$/', $otp);
                })
            );

        $mockControl->resendOtp('7654321@sit.singaporetech.edu.sg');

        $this->assertNotEmpty($_SESSION['otp']);
        $this->assertGreaterThan(time(), $_SESSION['otp_expiry']);
    }
}
