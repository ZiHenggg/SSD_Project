<?php
/**
 * testUpdatePasswordSuccess – Successfully updates password with correct old password and valid SIT email
 * testUpdatePasswordThrowsWhenStudentNotFound – Throws exception when student email not found
 * testUpdatePasswordThrowsWhenOldPasswordIncorrect – Throws exception when old password does not match
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\Entity\Student;

class UpdatePasswordTest extends TestCase
{
    private $studentRepo;
    private $studentControl;

    protected function setUp(): void
    {
        // Declare all abstract methods to avoid PHPUnit errors
        $this->studentRepo = $this->getMockBuilder(StudentRepository::class)
            ->onlyMethods([
                'getStudentById',
                'getStudentByEmail',
                'createStudentAccount',
                'isStudentExists',
                'updatePassword',
                'verifyStudentEmail',
                'enable2FAForUser',
                'disable2FA',
            ])
            ->getMock();

        $this->studentControl = new StudentControl($this->studentRepo);
    }

    private function isValidSitEmail(string $email): bool
    {
        return (bool)preg_match('/@sit\.singaporetech\.edu\.sg$/', $email);
    }

    public function testUpdatePasswordSuccess(): void
    {
        $email = 'user@sit.singaporetech.edu.sg';
        $oldPassword = 'OldPass123!';
        $newPassword = 'NewPass456!';

        $this->assertTrue($this->isValidSitEmail($email), 'Email domain must be @sit.singaporetech.edu.sg');

        $student = $this->createMock(Student::class);

        $this->studentRepo->method('getStudentByEmail')->with($email)->willReturn($student);

        $student->method('getPassword')->willReturn(password_hash($oldPassword, PASSWORD_DEFAULT));

        $this->studentRepo->expects($this->once())
            ->method('updatePassword')
            ->with(
                $email,
                $this->callback(function($hashedPassword) use ($newPassword) {
                    return password_verify($newPassword, $hashedPassword);
                })
            );

        $this->studentControl->updatePassword($email, $oldPassword, $newPassword);
    }

    public function testUpdatePasswordThrowsWhenStudentNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No student found with email');

        $email = 'noone@sit.singaporetech.edu.sg';

        $this->assertTrue($this->isValidSitEmail($email), 'Email domain must be @sit.singaporetech.edu.sg');

        $this->studentRepo->method('getStudentByEmail')->willReturn(null);

        $this->studentControl->updatePassword($email, 'anyOld', 'anyNew');
    }

    public function testUpdatePasswordThrowsWhenOldPasswordIncorrect(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Old password is incorrect.');

        $email = 'user@sit.singaporetech.edu.sg';
        $oldPassword = 'WrongOldPass!';
        $newPassword = 'NewPass456!';

        $this->assertTrue($this->isValidSitEmail($email), 'Email domain must be @sit.singaporetech.edu.sg');

        $student = $this->createMock(Student::class);
        $student->method('getPassword')->willReturn(password_hash('CorrectOldPass!', PASSWORD_DEFAULT));
        $this->studentRepo->method('getStudentByEmail')->with($email)->willReturn($student);

        $this->studentControl->updatePassword($email, $oldPassword, $newPassword);
    }
}
