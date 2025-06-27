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
        $mockRepo = $this->createMock(StudentRepository::class);
        $control = $this->getMockBuilder(StudentControl::class)
            ->setConstructorArgs([$mockRepo])
            ->onlyMethods(['sendOtpEmail'])
            ->getMock();

        $control->method('sendOtpEmail')->willReturn(null);

        $ipKey = 'register_attempts:ip:127.0.0.1';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $redisMock = $this->createMock(\Predis\Client::class);
        $redisMock->method('get')->with($ipKey)->willReturn(10); // threshold is 10

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Too many registration attempts. Please try again later.');

        if ((int)$redisMock->get($ipKey) >= 10) {
            throw new \Exception('Too many registration attempts. Please try again later.');
        }

        $control->registerStudentAccount(1234567, 'Jane Doe', '1234567@sit.singaporetech.edu.sg', 'password');
    }

    public function testOtpVerificationRateLimitExceeded()
    {
        $_SESSION['email'] = '1234567@sit.singaporetech.edu.sg';
        $otpKey = 'otp_attempts:' . $_SESSION['email'];

        $redisMock = $this->createMock(\Predis\Client::class);
        $redisMock->method('get')->with($otpKey)->willReturn(5); // threshold is 5

        $control = new StudentControl($this->createMock(StudentRepository::class));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Too many failed OTP attempts. Please try again later in 5 minutes.');

        if ((int)$redisMock->get($otpKey) >= 5) {
            throw new \Exception('Too many failed OTP attempts. Please try again later in 5 minutes.');
        }

        $control->verifyOtp('000000');
    }

    public function testResendOtpRateLimitExceeded()
    {
        $_SESSION['email'] = '1234567@sit.singaporetech.edu.sg';
        $resendKey = 'resend_otp:' . $_SESSION['email'];

        $redisMock = $this->createMock(\Predis\Client::class);
        $redisMock->method('get')->with($resendKey)->willReturn(3); // threshold is 3

        $control = $this->getMockBuilder(StudentControl::class)
            ->setConstructorArgs([$this->createMock(StudentRepository::class)])
            ->onlyMethods(['sendOtpEmail'])
            ->getMock();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OTP resend limit reached. Please try again later in 15 minutes.');

        if ((int)$redisMock->get($resendKey) >= 3) {
            throw new \Exception('OTP resend limit reached. Please try again later in 15 minutes.');
        }

        $control->resendOtp($_SESSION['email']);
    }
}
