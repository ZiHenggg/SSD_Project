<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;

class StudentControlTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session before each test
        $_SESSION = [];
    }

    public function testRegisterStudentStoresDataAndSendsOtp()
    {
        $mockRepo = $this->createMock(StudentRepository::class);

        $mockControl = $this->getMockBuilder(StudentControl::class)
            ->setConstructorArgs([$mockRepo])
            ->onlyMethods(['sendOtpEmail'])
            ->getMock();

        $mockControl->expects($this->once())
            ->method('sendOtpEmail')
            ->with(
                $this->equalTo('1234567@sit.singaporetech.edu.sg'),
                $this->callback(fn($otp) => is_string($otp) && preg_match('/^\d{6}$/', $otp) === 1)
            );

        $mockControl->registerStudentAccount(
            1234567,
            'Jane Doe',
            '1234567@sit.singaporetech.edu.sg',
            'securePassword!'
        );

        $pending = $_SESSION['pending_registration'];
        $this->assertEquals(1234567, $pending['studentId']);
        $this->assertEquals('Jane Doe', $pending['studentName']);
        $this->assertEquals('1234567@sit.singaporetech.edu.sg', $pending['email']);
        $this->assertTrue(password_verify('securePassword!', $pending['password']));

        $this->assertNotEmpty($_SESSION['otp']);
        $this->assertIsNumeric($_SESSION['otp']);
        $this->assertGreaterThan(time(), $_SESSION['otp_expiry']);
    }
}
