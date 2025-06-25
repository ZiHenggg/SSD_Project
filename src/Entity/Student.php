<?php

namespace App\Entity;

class Student {
    private int $studentId;
    private string $studentName;
    private string $email;
    private string $password;

    private ?string $google2fa_secret = null;
    private bool $is_2fa_enabled = false;

    // Constructor
    public function __construct(
        int $studentId,
        string $studentName,
        string $email,
        string $password,
        ?string $google2fa_secret = null,
        bool $is_2fa_enabled = false
    ) {
        $this->setStudentId($studentId);
        $this->setStudentName($studentName);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->google2fa_secret = $google2fa_secret;
        $this->is_2fa_enabled = $is_2fa_enabled;
    }

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

    public function get2FASecret(): ?string {
        return $this->google2fa_secret;
    }

    public function is2FAEnabled(): bool {
        return $this->is_2fa_enabled;
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

    // Password check (only if you're using plain-text for now)
    public function checkPassword(string $password): bool {
        return $this->password === $password;
    }
}
?>
