<?php

/**
 * Tests the OTP registration flow logic in StudentControl:
 *
 * ✅ verifyOtp()
 * - Successfully registers student with valid OTP
 * - Fails for expired OTP
 * - Fails for invalid OTP
 *
 * ✅ resendOtp()
 * - Regenerates new 6-digit OTP and updates expiry
 * - Sends new OTP email to student's SIT email
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\SessionManager;

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
        SessionManager::setOTP('654321', time() + 600); // ✅ Matches real logic
        SessionManager::setRegistration([
            'studentId' => 7654321,
            'studentName' => 'Alice Tan',
            'email' => '7654321@sit.singaporetech.edu.sg',
            'password' => password_hash('testpass123', PASSWORD_DEFAULT)
        ]);

        $mockRepo = $this->createMock(StudentRepository::class);
        $mockRepo->expects($this->once())->method('createStudentAccount');
        $mockRepo->expects($this->once())->method('verifyStudentEmail')
                 ->with('7654321@sit.singaporetech.edu.sg');

        $control = new StudentControl($mockRepo);
        $result = $control->verifyOtp('654321');

        $this->assertTrue($result['success']);
        $this->assertEquals('Registration complete!', $result['message']);
        $this->assertNull(SessionManager::getOTP());   // ✅ FIXED: getOTP not getRegisterOTP
        $this->assertNull(SessionManager::getRegistration());
    }

    public function testExpiredOtpFailsVerification()
    {
        SessionManager::setOTP('123456', time() - 1); // ✅ Matches real logic
        SessionManager::setRegistration([]);

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $result = $control->verifyOtp('123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('OTP expired.', $result['message']);
    }

    public function testInvalidOtpFailsVerification()
    {
        SessionManager::setOTP('111111', time() + 600); // ✅ Matches real logic
        SessionManager::setRegistration([]);

        $control = new StudentControl($this->createMock(StudentRepository::class));
        $result = $control->verifyOtp('999999');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid OTP.', $result['message']);
    }

    public function testResendOtpGeneratesNewCode()
    {
        SessionManager::setRegistration([
            'studentId' => 7654321,
            'studentName' => 'Alice Tan',
            'email' => '7654321@sit.singaporetech.edu.sg',
            'password' => 'hashedpass'
        ]);

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
                    return preg_match('/^\d{6}$/', $otp) === 1;
                })
            );

        $mockControl->resendOtp('7654321@sit.singaporetech.edu.sg');

        $otpData = SessionManager::getOTP();
        $this->assertNotEmpty($otpData['code']);
        $this->assertGreaterThan(time(), $otpData['expiry']);
    }
}
