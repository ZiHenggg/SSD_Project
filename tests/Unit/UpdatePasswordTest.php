<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Control\StudentControl;
use App\Repository\StudentRepository;
use App\Entity\Student;

class StudentControlTest extends TestCase
{
    private $studentRepo;
    private $studentControl;

    protected function setUp(): void
    {
        $this->studentRepo = $this->getMockBuilder(StudentRepository::class)
            ->onlyMethods(['getStudentByEmail', 'updatePassword'])
            ->getMock();

        $this->studentControl = new StudentControl($this->studentRepo);
    }

    public function testUpdatePasswordSuccess(): void
    {
        $email = 'user@example.com';
        $oldPassword = 'OldPass123!';
        $newPassword = 'NewPass456!';

        $student = $this->createMock(Student::class);

        // Mock to return a student entity
        $this->studentRepo->method('getStudentByEmail')->with($email)->willReturn($student);

        // Mock password hash (assume getPassword() returns the hashed old password)
        $student->method('getPassword')->willReturn(password_hash($oldPassword, PASSWORD_DEFAULT));

        // Expect updatePassword to be called once with new hashed password
        $this->studentRepo->expects($this->once())
            ->method('updatePassword')
            ->with(
                $email,
                $this->callback(function($hashedPassword) use ($newPassword) {
                    return password_verify($newPassword, $hashedPassword);
                })
            );

        // Call method under test
        $this->studentControl->updatePassword($email, $oldPassword, $newPassword);
    }

    public function testUpdatePasswordThrowsWhenStudentNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No student found with email');

        $this->studentRepo->method('getStudentByEmail')->willReturn(null);

        $this->studentControl->updatePassword('nonexistent@example.com', 'anyOld', 'anyNew');
    }

    public function testUpdatePasswordThrowsWhenOldPasswordIncorrect(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Old password is incorrect.');

        $email = 'user@example.com';
        $oldPassword = 'WrongOldPass!';
        $newPassword = 'NewPass456!';

        $student = $this->createMock(Student::class);
        $student->method('getPassword')->willReturn(password_hash('CorrectOldPass!', PASSWORD_DEFAULT));
        $this->studentRepo->method('getStudentByEmail')->with($email)->willReturn($student);

        $this->studentControl->updatePassword($email, $oldPassword, $newPassword);
    }
}
