<?php

/**
 * ✅ StudentControlLoginTest
 *
 * This test suite verifies all login logic in StudentControl::loginStudent().
 *
 * Covers:
 * - Login fails with non-existent email
 * - Login fails with incorrect password
 * - Login succeeds with valid credentials and 2FA enabled
 * - Login succeeds with valid credentials and no 2FA
 *
 * 🔐 Ensures:
 * - Passwords are validated securely
 * - Session data is set correctly
 * - Users are redirected based on 2FA status
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\Entity\Student;
use App\SessionManager;

class StudentControlLoginTest extends TestCase
{
    private $studentRepoMock;
    private $studentControl;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        $this->studentRepoMock = $this->createMock(StudentRepository::class);
        $this->studentControl = new StudentControl($this->studentRepoMock);
    }

    public function testLoginFailsWithInvalidEmail()
    {
        $this->studentRepoMock->method('getStudentByEmail')
            ->with('invalid@sit.singaporetech.edu.sg')
            ->willReturn(null);

        $result = $this->studentControl->loginStudent('invalid@sit.singaporetech.edu.sg', 'any-password');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid email or password.', $result['message']);
    }

    public function testLoginFailsWithWrongPassword()
    {
        $student = $this->createMock(Student::class);
        $student->method('getPassword')->willReturn(password_hash('correct-password', PASSWORD_DEFAULT));

        $this->studentRepoMock->method('getStudentByEmail')
            ->with('user@sit.singaporetech.edu.sg')
            ->willReturn($student);

        $result = $this->studentControl->loginStudent('user@sit.singaporetech.edu.sg', 'wrong-password');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid email or password.', $result['message']);
    }

    public function testLoginWith2FAEnabled()
    {
        $student = $this->createConfiguredMock(Student::class, [
            'getEmail' => 'user2fa@sit.singaporetech.edu.sg',
            'getPassword' => password_hash('mypassword', PASSWORD_DEFAULT),
            'is2FAEnabled' => true
        ]);

        $this->studentRepoMock->method('getStudentByEmail')
            ->with('user2fa@sit.singaporetech.edu.sg')
            ->willReturn($student);

        $result = $this->studentControl->loginStudent('user2fa@sit.singaporetech.edu.sg', 'mypassword');

        $this->assertTrue($result['success']);
        $this->assertEquals('verify_2fa.php', $result['redirect']);
        $this->assertEquals('user2fa@sit.singaporetech.edu.sg', SessionManager::get2FA()['pending_email']);
    }

    public function testLoginWith2FANotEnabled()
    {
        $student = $this->createConfiguredMock(Student::class, [
            'getStudentId' => 2001,
            'getStudentName' => 'Jane SIT',
            'getEmail' => 'jane@sit.singaporetech.edu.sg',
            'getPassword' => password_hash('secure123', PASSWORD_DEFAULT),
            'is2FAEnabled' => false
        ]);

        $this->studentRepoMock->method('getStudentByEmail')
            ->with('jane@sit.singaporetech.edu.sg')
            ->willReturn($student);

        $result = $this->studentControl->loginStudent('jane@sit.singaporetech.edu.sg', 'secure123');

        $this->assertTrue($result['success']);
        $this->assertEquals('setup_2fa.php', $result['redirect']);

        $this->assertEquals([
            'id' => 2001,
            'email' => 'jane@sit.singaporetech.edu.sg',
            'name' => 'Jane SIT'
        ], SessionManager::getUser());

        $this->assertNull(SessionManager::get2FA()['secret']);
    }
}
