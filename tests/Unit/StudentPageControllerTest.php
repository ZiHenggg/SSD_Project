<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Boundary\StudentPageController;
use App\Control\StudentControl;

class StudentPageControllerTest extends TestCase
{
    protected $controller;

    protected function setUp(): void
    {
        $mockControl = $this->createMock(StudentControl::class);
        $this->controller = new StudentPageController($mockControl);
    }

    public function testEmptyFieldsValidation()
    {
        $data = [
            'studentId' => '',
            'studentName' => '',
            'email' => '',
            'password' => '',
        ];

        $error = $this->controller->validateStudentInput($data);
        $this->assertNotEmpty($error, "Validation should fail with empty fields.");
    }

    public function testInvalidEmailValidation()
    {
        $data = [
            'studentId' => '12345678',
            'studentName' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'pass123',
        ];

        $error = $this->controller->validateStudentInput($data);
        $this->assertStringContainsString('email', $error);
    }

    public function testValidInputPassesValidation()
    {
        $data = [
            'studentId' => '12345678',
            'studentName' => 'Jane Doe',
            'email' => '12345678@example.com',
            'password' => 'securePass1!',
        ];

        $error = $this->controller->validateStudentInput($data);
        $this->assertNull($error, "Valid input should not return an error.");
    }
}
