<?php

/**
 * Class StudentPageControllerTest
 *
 * ✅ Unit tests for validating student registration input
 * Covers `validateStudentInput` from StudentPageController:
 *
 * - ❌ Detects missing fields
 * - ❌ Catches invalid email format
 * - ✔️ Accepts valid SIT email and matching Student ID
 */

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

    /** ❌ Fails if required fields are left empty */
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

    /** ❌ Fails if email is not a valid format */
    public function testInvalidEmailValidation()
    {
        $data = [
            'studentId' => '1234567',
            'studentName' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'pass123',
        ];

        $error = $this->controller->validateStudentInput($data);
        $this->assertStringContainsString('email', $error);
    }

    /** ✔️ Passes when all inputs are valid (SIT email + strong password) */
    public function testValidInputPassesValidation()
    {
        $data = [
            'studentId' => '1234567',
            'studentName' => 'Jane Doe',
            'email' => '1234567@sit.singaporetech.edu.sg',
            'password' => 'securePass1!',
        ];

        $error = $this->controller->validateStudentInput($data);
        $this->assertNull($error, "Valid input should not return an error.");
    }
}
