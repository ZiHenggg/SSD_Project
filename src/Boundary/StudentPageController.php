<?php
namespace App\Boundary;

use App\Control\StudentControl;
use App\Entity\Student;

class StudentPageController
{
    private StudentControl $studentControl;

    public function __construct(StudentControl $studentControl)
    {
        $this->studentControl = $studentControl;
    }

    public function validateStudentInput(array $data): ?string
    {
        $studentId = isset($data['studentId']) ? (int) $data['studentId'] : 0;
        $studentName = trim($data['studentName'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (!preg_match('/^\d{7}$/', (string) $studentId)) {
            return "Invalid student ID. It must be 7 digits long.";
        }

        if (empty($studentName) || empty($email) || empty($password)) {
            return "All fields are required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        if (!preg_match('/^[\w\.\-]+@sit\.singaporetech\.edu\.sg$/', $email)) {
            return "Email must be a SIT address.";
        }

        return null;
    }

    public function loginStudent(array $postLogin): array
    {
        $email = trim($postLogin['email'] ?? '');
        $password = trim($postLogin['password'] ?? '');

        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format.'];
        }

        return $this->studentControl->loginStudent($email, $password);
    }

    public function showUserProfile(int $studentId): ?Student
    {
        return $this->studentControl->getStudentById($studentId);
    }
}
