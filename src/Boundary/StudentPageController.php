<?php
namespace App\Boundary;
use App\Control\StudentControl;
use App\Entity\Student;
class StudentPageController
{
    private string $studentId;
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
            /*
            Email must be a SIT address.
            The email must: 
                - Start with one or more allowed characters:
                - Letters, digits, underscores, hyphens, or dots
                - Must then end with @sit.singaporetech.edu.sg
            */
            return "Email must be a SIT address.";
        }

        // TODO: Add password validation rules

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

        // Check if the student already exists
        if ($this->studentControl->checkStudentExist((string) $studentId) || $this->studentControl->checkStudentExist($email)) {
            return "Student already exists.";
        }

        // Check if Email and Student ID match
        $prefix = explode('@', $email)[0]; // get the part before "@"
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

    // public function loginStudent(array $postLogin): string {
    // $email = trim($postLogin['email'] ?? '');
    // $password = trim($postLogin['password'] ?? '');

    // if (empty($email) || empty($password)) {
    //     return "Email and password are required.";
    // }

    // // Validate email format
    // if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    //     return "Invalid email format.";
    // }

    // // Retrieve student by email
    // $student = $this->studentControl->getStudentByEmail($email);
    // if (!$student || !password_verify($password, $student->getPassword())) {
    //     return "Invalid email or password.";
    // }

    // // Set session variables
    // $_SESSION['email'] = (string)$email;
    // $_SESSION['studentName'] = $student->getStudentName();

    //     return "Login successful. Welcome, " . htmlspecialchars($student->getStudentName()) . "!";
    // }
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
        try {
            $student = $this->studentControl->getStudentById($studentId);
            if (!$student) {
                return null; // Student not found
            }
            return $student;
        } catch (\Exception $e) {
            throw new \Exception('An error occurred while fetching the student profile: ' . $e->getMessage());
        }
    }

    public function updatePassword(array $postData): array
    {
        $email = $_SESSION['user']['email'] ?? null;
        
        if (!$email) {
            return ['success' => false, 'message' => 'User not authenticated.'];
        }

        $oldPassword = trim($postData['old_password'] ?? '');
        $newPassword = trim($postData['new_password'] ?? '');

        if (!$oldPassword || !$newPassword) {
            return ['success' => false, 'message' => 'Both fields are required.'];
        }

        try {
            $this->studentControl->updatePassword($email, $oldPassword, $newPassword);
            return ['success' => true, 'message' => 'Password updated successfully.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
