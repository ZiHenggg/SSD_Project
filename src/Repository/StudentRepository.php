<?php
namespace App\Repository;

use App\Entity\Student; // Assuming Student is an entity class representing a student

interface StudentRepository
{

    public function getStudentById(string $studentId): ?Student;

    public function getStudentByEmail(string $email): ?Student;

    public function getAllStudents(): array;

    public function createStudentAccount(Student $student): void;

    public function isStudentExists(string $identifier): bool;
}
?>
