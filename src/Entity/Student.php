<?php

namespace Ngmin\Ict2216G5\Entity;

class Student {
    private int $studentId;
    private string $studentName;
    private string $email;
    private string $password;

    // Getters
    public function getStudentId(): int {
        return $this->studentId;
    }

    public function getStudentName(): string {
        return $this->studentName;
    }
    public function getEmail(): string {
        return $this->email;
    }
    public function getPassword(): string {
        return $this->password;
    }

    // Setters
    private function setStudentId(int $studentId): void {
        $this->studentId = $studentId;
    }
    private function setStudentName(string $studentName): void {
        $this->studentName = $studentName;
    }
    private function setEmail(string $email): void {
        $this->email = $email;
    }
    private function setPassword(string $password): void {
        $this->password = $password;
    }

    public function __construct(int $studentId, string $studentName, string $email, string $password) {
        $this->setStudentId($studentId);
        $this->setStudentName($studentName);
        $this->setEmail($email);
        $this->setPassword($password);
    }

    public function checkPassword(string $password): bool {
        return $this->password === $password;
    }
}
?>