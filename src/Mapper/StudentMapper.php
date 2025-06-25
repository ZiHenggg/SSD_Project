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

        return $result ? $this->mapRowToStudent($result) : null;
    }

    public function getStudentByEmail(string $email): ?Student
    {
        $stmt = $this->dbConnection->prepare("SELECT * FROM students WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $this->mapRowToStudent($result) : null;
    }

    public function getAllStudents(): array
    {
        $stmt = $this->dbConnection->query("SELECT * FROM students");
        $students = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = $this->mapRowToStudent($row);
        }

        return $students;
    }

    public function createStudentAccount(Student $student): void
    {
        $stmt = $this->dbConnection->prepare("
            INSERT INTO students 
            (studentId, name, email, password, google2fa_secret, is_2fa_enabled, otp_code, otp_expiry, email_verified) 
            VALUES 
            (:studentId, :name, :email, :password, :secret, :is2fa, :otp, :otp_expiry, :verified)
        ");

        $stmt->execute([
            ':studentId' => $student->getStudentId(),
            ':name' => $student->getStudentName(),
            ':email' => $student->getEmail(),
            ':password' => $student->getPassword(),
            ':secret' => $student->get2FASecret(),
            ':is2fa' => $student->is2FAEnabled() ? 1 : 0,
            ':otp' => $student->getOtpCode(),
            ':otp_expiry' => $student->getOtpExpiry(),
            ':verified' => $student->isEmailVerified() ? 1 : 0
        ]);
    }

    public function isStudentExists(string $identifier): bool
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM students WHERE email = :email");
            $stmt->bindParam(':email', $identifier);
        } else {
            $stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM students WHERE studentId = :studentId");
            $stmt->bindParam(':studentId', $identifier);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function verifyStudentEmail(string $email): void
    {
        $stmt = $this->dbConnection->prepare("
            UPDATE students 
            SET email_verified = 1, otp_code = NULL, otp_expiry = NULL 
            WHERE email = :email
        ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }

    private function mapRowToStudent(array $row): Student
    {
        return new Student(
            (int) $row['studentId'],
            $row['name'],
            $row['email'],
            $row['password'],
            $row['otp_code'] ?? null,
            $row['otp_expiry'] ?? null,
            isset($row['email_verified']) ? (bool) $row['email_verified'] : false,
            $row['google2fa_secret'] ?? null,
            isset($row['is_2fa_enabled']) ? (bool) $row['is_2fa_enabled'] : false
        );
    }

    public function updatePassword(string $email, string $hashedPassword): void
    {
        $stmt = $this->dbConnection->prepare("UPDATE students SET password = :password WHERE email = :email");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
}
