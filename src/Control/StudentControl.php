<?php
namespace App\Control;
use App\Entity\Student;
use App\Repository\StudentRepository;

class StudentControl
{
    private StudentRepository $studentRepo;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepo = $studentRepo;
    }

    public function getStudentById(string $studentId): ?Student
    {
        // Retrieve the student by ID
        $student = $this->studentRepo->getStudentById($studentId);

        // If student does not exist, return null
        if (!$student) {
            return null;
        }

        // Return the student object
        return $student;
    }

    public function getStudentByEmail(string $email): ?Student
    {
        // Retrieve the student by email
        $student = $this->studentRepo->getStudentByEmail($email);

        // If student does not exist, return null
        if (!$student) {
            return null;
        }

        // Return the student object
        return $student;
    }

    public function checkStudentExist(string $identifier): bool
    {
        return $this->studentRepo->isStudentExists($identifier);
    }

    public function registerStudentAccount(int $studentId, string $studentName, string $email, string $password): void
    {
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // $student = new Student($studentId, $studentName, $email, $hashedPassword);

        // Create a new student object
        //$student = new Student($studentId, $studentName, $email, $password);

        // Check if the student already exists
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $student = new Student($studentId, $studentName, $email, $hashedPassword);


        // Add the student to the repository
        $this->studentRepo->createStudentAccount($student);
    }

    public function loginStudent(string $email, string $password): array
    {
        $student = $this->studentRepo->getStudentByEmail($email);

        // // Basic password check (no hashing for now)
        // if (!$student || $password !== $student->getPassword()) {
        //     return ['success' => false, 'message' => 'Invalid email or password.'];
        // }

        // Verify the password using password_verify
        if (!$student || !password_verify($password, $student->getPassword())) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Check if 2FA is enabled
        if (method_exists($student, 'is2FAEnabled') && $student->is2FAEnabled()) {
            $_SESSION['pending_2fa_email'] = $student->getEmail();
            return ['success' => true, 'redirect' => 'verify_2fa.php'];
        }

        // No 2FA — log in immediately and proceed to setup
        $_SESSION['user'] = [
            'id' => $student->getStudentId(),
            'email' => $student->getEmail(),
            'name' => $student->getStudentName()
        ];

        return ['success' => true, 'redirect' => 'setup_2fa.php'];
    }

    public function deleteStudent(string $studentId): void
    {
        // Check if the student exists before attempting to remove
        if ($this->studentRepo->isStudentExists($studentId)) {
            // $this->studentRepo->removeStudent($studentId);
        } else {
            throw new \Exception("Student with ID $studentId does not exist.");
        }
    }

    // Update student profile?

    // Send Reset Token
    public function sendResetToken(string $email): void
    {
        // Check if the student exists by email
        $student = $this->studentRepo->getStudentByEmail($email);
        if (!$student) {
            throw new \Exception("No student found with email $email.");
        }

        // TODO: Implement logic to send reset token to the student's email
    }

    // Update Password
    public function updatePassword(string $email, string $oldPassword, string $newPassword): void
    {
        // Check if the student exists by email
        $student = $this->studentRepo->getStudentByEmail($email);
        if (!$student) {
            throw new \Exception("No student found with email $email.");
        }

        // TODO: Implement logic to verify old password and update to new password

        // Verify old password
        if (!password_verify($oldPassword, $student->getPassword())) {
            throw new \Exception("Old password is incorrect.");
        }

        // Hash the new password
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $this->studentRepo->updatePassword($email, $hashedNewPassword);
    }

    public function get2FASecret(string $email): string
    {
        $student = $this->studentRepo->getStudentByEmail($email);

        if (!$student || !$student->is2FAEnabled()) {
            throw new \Exception("2FA is not set up for this account.");
        }

        return $student->get2FASecret();
    }
}
?>