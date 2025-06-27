<?php
/**
 * Class StudentControlVerify2FATest
 *
 * ✅ Unit tests for the 2FA verification process during login
 * Covers the `verify2FACode` method in StudentControl:
 * 
 * - ✔️ Successful verification using valid 2FA code
 * - ❌ Rejection if 2FA code is invalid
 * - ❌ Rejection if user has not enabled 2FA
 * - ❌ Rejection if email does not exist
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\Entity\Student;
use RobThree\Auth\TwoFactorAuth;

class StudentControlVerify2FATest extends TestCase
{
    private $studentRepoMock;
    private $studentControl;

    protected function setUp(): void
    {
        $this->studentRepoMock = $this->createMock(StudentRepository::class);
        $this->studentControl = new StudentControl($this->studentRepoMock);
        $_SESSION = [];
    }

    /** ✔️ Verifies 2FA successfully with a valid TOTP code */
    public function testVerify2FAWithCorrectCode()
    {
        $secret = (new TwoFactorAuth())->createSecret();
        $validCode = (new TwoFactorAuth())->getCode($secret);

        $student = $this->createConfiguredMock(Student::class, [
            'getEmail' => 'user@sit.singaporetech.edu.sg',
            'getStudentId' => 1001,
            'getStudentName' => 'Test User',
            'is2FAEnabled' => true,
            'get2FASecret' => $secret
        ]);

        $this->studentRepoMock->method('getStudentByEmail')->willReturn($student);

        $result = $this->studentControl->verify2FACode('user@sit.singaporetech.edu.sg', $validCode);

        $this->assertTrue($result['success']);
        $this->assertEquals('dashboard.php', $result['redirect']);
        $this->assertEquals(1001, $_SESSION['user']['id']);
    }

    /** ❌ Fails with an incorrect code */
    public function testVerify2FAWithWrongCode()
    {
        $secret = (new TwoFactorAuth())->createSecret();
        $wrongCode = '123456';

        $student = $this->createConfiguredMock(Student::class, [
            'is2FAEnabled' => true,
            'get2FASecret' => $secret
        ]);

        $this->studentRepoMock->method('getStudentByEmail')->willReturn($student);

        $result = $this->studentControl->verify2FACode('user@sit.singaporetech.edu.sg', $wrongCode);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid 2FA code.', $result['message']);
    }

    /** ❌ Fails when 2FA is not enabled for the user */
    public function testVerify2FAWithNo2FAEnabled()
    {
        $student = $this->createConfiguredMock(Student::class, [
            'is2FAEnabled' => false
        ]);

        $this->studentRepoMock->method('getStudentByEmail')->willReturn($student);

        $result = $this->studentControl->verify2FACode('user@sit.singaporetech.edu.sg', '123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('2FA is not set up for this account.', $result['message']);
    }

    /** ❌ Fails when the email is not found */
    public function testVerify2FAWithInvalidEmail()
    {
        $this->studentRepoMock->method('getStudentByEmail')->willReturn(null);

        $result = $this->studentControl->verify2FACode('invalid@sit.singaporetech.edu.sg', '123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('2FA is not set up for this account.', $result['message']);
    }
}
