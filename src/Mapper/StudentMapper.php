<?php
namespace App\Mapper;
use App\Entity\Student;
use App\Repository\StudentRepository;
use PDO;

class StudentMapper implements StudentRepository
{
    private PDO $dbConnection;

    public function __construct(PDO $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function getStudentById(string $studentId): ?Student
    {
        $stmt = $this->dbConnection->prepare("SELECT * FROM students WHERE studentId = :studentId");
        $stmt->bindParam(':studentId', $studentId);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new Student(
                (int) $result['studentId'],
                $result['name'],
                $result['email'],
                $result['password'],
                $result['google2fa_secret'] ?? null,
                (bool) $result['is_2fa_enabled'] ?? false
            );
        }

        return null; // Return null if no student found
    }

    public function getStudentByEmail(string $email): ?Student
    {
        $stmt = $this->dbConnection->prepare("SELECT * FROM students WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
              return new Student(
                (int) $result['studentId'],
                $result['name'],
                $result['email'],
                $result['password'],
                $result['google2fa_secret'] ?? null,
                (bool) $result['is_2fa_enabled'] ?? false
            );
        }

        return null; // Return null if no student found
    }
    public function getAllStudents(): array
    {
        $stmt = $this->dbConnection->query("SELECT * FROM students");
        $students = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = new Student(
                (int) $row['studentId'],
                $row['name'],
                $row['email'],
                $row['password']
            );
        }

        return $students; // Return an array of Student objects
    }

    public function createStudentAccount(Student $student): void
    {
        $studentId = $student->getStudentId();
        $studentName = $student->getStudentName();
        $email = $student->getEmail();
        $password = $student->getPassword(); // TODO: hash password before storing

        if (!$this->isStudentExists($studentId)) {
            // Check if the student already exists before adding
            // If not, add the student to the repository
            $stmt = $this->dbConnection->prepare("INSERT INTO students (studentId, name, email, password) VALUES (:studentId, :studentName, :email, :password)");
            $stmt->bindValue(':studentId', $studentId);
            $stmt->bindValue(':studentName', $studentName);
            $stmt->bindValue(':email', $email);

            $stmt->bindValue(':password', $password);
            $stmt->execute();
        } else {
            throw new \Exception("Student with ID " . $student->getStudentId() . " already exists.");
        }
    }

    public function isStudentExists(string $identifier): bool
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // It's an email
            $stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM students WHERE email = :email");
            $stmt->bindParam(':email', $identifier);
        } else {
            // Assume it's a student ID
            $stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM students WHERE studentId = :studentId");
            $stmt->bindParam(':studentId', $identifier);
        }

        $stmt->execute();
        $count = $stmt->fetchColumn();

        return $count > 0;
    }

    public function updatePassword(string $email, string $hashedPassword): void
    {
        $stmt = $this->dbConnection->prepare("UPDATE students SET password = :password WHERE email = :email");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
}
?>
