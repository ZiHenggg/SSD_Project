<?php
namespace App\Boundary;

use App\Control\StudentControl;
use App\Entity\Student;
use DivineOmega\PasswordExposed\Enums\PasswordStatus;

class StudentPageController
{
    private StudentControl $studentControl;

    public function __construct(StudentControl $studentControl)
    {
        $this->studentControl = $studentControl;
    }

    public function validatePassword(string $password): ?string
    {
        if (strlen($password) < 8 || strlen($password) > 64) {
            return "Password must be between 8 and 64 characters.";
        }

        $status = password_exposed($password);

        switch ($status) {
            case PasswordStatus::EXPOSED:
                return "This password has been found in a known data breach. Please choose a more secure one.";
            case PasswordStatus::UNKNOWN:
                return "Unable to verify password security at this time. Try again later.";
        }

        return null;
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

        $passwordError = $this->validatePassword($password);
        if ($passwordError !== null) {
            return $passwordError;
        }

        return null;
    }

    public function registerStudent(array $postStudent): string
    {
        $error = $this->validateStudentInput($postStudent);
        if ($error !== null) {
            return $error;
        }

        $studentId = (int) $postStudent['studentId'];
        $studentName = trim($postStudent['studentName']);
        $email = trim($postStudent['email']);
        $password = trim($postStudent['password']);

        if ($this->studentControl->checkStudentExist((string) $studentId) || $this->studentControl->checkStudentExist($email)) {
            return "Student already exists.";
        }

        $prefix = explode('@', $email)[0];
        if ($prefix !== (string) $studentId) {
            return "Email must begin with your Student ID.";
        }

        try {
            $this->studentControl->registerStudentAccount($studentId, $studentName, $email, $password);
            return "Account created successfully!";
        } catch (\Exception $e) {
            return "Error creating student account: " . $e->getMessage();
        }
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

    public function updatePassword(string $email, array $postData): array
    {
        if (!$email) {
            return ['success' => false, 'message' => 'User not authenticated.'];
        }

        $oldPassword = trim($postData['old_password'] ?? '');
        $newPassword = trim($postData['new_password'] ?? '');

        if (!$oldPassword || !$newPassword) {
            return ['success' => false, 'message' => 'Both fields are required.'];
        }

        if (hash_equals($oldPassword, $newPassword)) {
            return ['success' => false, 'message' => 'New password must be different from the old password.'];
        }

        $newPasswordError = $this->validatePassword($newPassword);
        if ($newPasswordError !== null) {
            return ['success' => false, 'message' => $newPasswordError];
        }

        try {
            $this->studentControl->updatePassword($email, $oldPassword, $newPassword);
            return ['success' => true, 'message' => 'Password updated successfully.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function get2FASecretForEmail(string $email): string
    {
        return $this->studentControl->get2FASecret($email);
    }

    // ✅ NEW: Verifies a 2FA code using a secret (for password update context)
    public function verify2FACodeForPasswordUpdate(string $secret, string $code): bool
    {
        return $this->studentControl->verify2FACodeWithSecret($secret, $code);
    }
}
