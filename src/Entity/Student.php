<?php

namespace App\Entity;

class Student {
    private int $studentId;
    private string $studentName;
    private string $email;
    private string $password;

    private ?string $otp_code = null;
    private ?string $otp_expiry = null;
    private bool $is_email_verified = false;

    private ?string $google2fa_secret = null;
    private bool $is_2fa_enabled = false;

    public function __construct(
        int $studentId,
        string $studentName,
        string $email,
        string $password,
        ?string $otp_code = null,
        ?string $otp_expiry = null,
        bool $is_email_verified = false,
        ?string $google2fa_secret = null,
        bool $is_2fa_enabled = false
    ) {
        $this->studentId = $studentId;
        $this->studentName = $studentName;
        $this->email = $email;
        $this->password = $password;
        $this->otp_code = $otp_code;
        $this->otp_expiry = $otp_expiry;
        $this->is_email_verified = $is_email_verified;
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

    public function getOtpCode(): ?string {
        return $this->otp_code;
    }

    public function getOtpExpiry(): ?string {
        return $this->otp_expiry;
    }

    public function isEmailVerified(): bool {
        return $this->is_email_verified;
    }

    public function get2FASecret(): ?string {
        return $this->google2fa_secret;
    }

    public function is2FAEnabled(): bool {
        return $this->is_2fa_enabled;
    }

    // Setters
    public function setOtpCode(?string $otp): void {
        $this->otp_code = $otp;
    }

    public function setOtpExpiry(?string $expiry): void {
        $this->otp_expiry = $expiry;
    }

    public function setEmailVerified(bool $verified): void {
        $this->is_email_verified = $verified;
    }

    public function set2FASecret(?string $secret): void {
        $this->google2fa_secret = $secret;
    }

    public function set2FAEnabled(bool $enabled): void {
        $this->is_2fa_enabled = $enabled;
    }

    // For plain-text testing only (not used if bcrypt is enabled)
    public function checkPassword(string $password): bool {
        return $this->password === $password;
    }
}
?>
