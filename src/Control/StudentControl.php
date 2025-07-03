<?php
namespace App\Control;

use App\Entity\Student;
use App\Repository\StudentRepository;
use App\SessionManager;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use RobThree\Auth\TwoFactorAuth;

class StudentControl
{
    private StudentRepository $studentRepo;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepo = $studentRepo;
    }

    public function getStudentById(int $studentId): ?Student
    {
        return $this->studentRepo->getStudentById($studentId) ?: null;
    }

    public function registerStudentAccount(int $studentId, string $studentName, string $email, string $password): void
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $otp = strval(random_int(100000, 999999));
        $otpExpiry = (new \DateTime('+10 minutes'))->getTimestamp();

        SessionManager::setRegistration([
            'studentId' => $studentId,
            'studentName' => $studentName,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        SessionManager::setOTP($otp, $otpExpiry);

        $this->sendOtpEmail($email, $otp);
    }

    protected function sendOtpEmail(string $to, string $otp): void
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['EMAIL_USERNAME'];
            $mail->Password = $_ENV['EMAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['EMAIL_USERNAME'], 'SSD App');
            $mail->addAddress($to);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Your OTP is: $otp\nIt expires in 10 minutes.";

            $mail->send();
        } catch (Exception $e) {
            throw new \Exception("OTP email failed: {$mail->ErrorInfo}");
        }
    }

    public function verifyOtp(string $inputOtp): array
    {
        $otpSession = SessionManager::getOTP();
        $registration = SessionManager::getRegistration();

        if (!$otpSession || !$registration) {
            return ['success' => false, 'message' => 'Session expired. Please try again.'];
        }

        if (time() > $otpSession['expiry']) {
            return ['success' => false, 'message' => 'OTP expired.'];
        }

        if ($otpSession['code'] !== $inputOtp) {
            return ['success' => false, 'message' => 'Invalid OTP.'];
        }

        $student = new Student(
            $registration['studentId'],
            $registration['studentName'],
            $registration['email'],
            $registration['password']
        );

        $this->studentRepo->createStudentAccount($student);
        $this->studentRepo->verifyStudentEmail($student->getEmail());

        SessionManager::remove('otp');
        SessionManager::remove('registration');

        return ['success' => true, 'message' => 'Registration complete!'];
    }

    public function loginStudent(string $email, string $password): array
    {
        $student = $this->studentRepo->getStudentByEmail($email);

        if (!$student || !password_verify($password, $student->getPassword())) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        if ($student->is2FAEnabled()) {
            SessionManager::set2FA($student->getEmail());
            return ['success' => true, 'redirect' => 'verify_2fa.php'];
        }

        SessionManager::setUser([
            'id' => $student->getStudentId(),
            'email' => $student->getEmail(),
            'name' => $student->getStudentName(),
        ]);

        SessionManager::set2FA(null, null);

        return ['success' => true, 'redirect' => 'setup_2fa.php'];
    }

    public function verify2FACode(string $email, string $code): array
    {
        $student = $this->studentRepo->getStudentByEmail($email);
        if (!$student || !$student->is2FAEnabled()) {
            return ['success' => false, 'message' => '2FA is not set up for this account.'];
        }

        $tfa = new TwoFactorAuth('SSD App');
        if ($tfa->verifyCode($student->get2FASecret(), $code)) {
            SessionManager::setUser([
                'id' => $student->getStudentId(),
                'email' => $student->getEmail(),
                'name' => $student->getStudentName()
            ]);
            SessionManager::remove('2fa');

            return ['success' => true, 'redirect' => 'dashboard.php'];
        }

        return ['success' => false, 'message' => 'Invalid 2FA code.'];
    }

    public function verify2FACodeWithSecret(string $secret, string $code): bool
    {
        $tfa = new TwoFactorAuth('SSD App');
        return $tfa->verifyCode($secret, $code);
    }

    public function confirm2FASetup(string $email, string $code, string $secret): bool
    {
        $tfa = new TwoFactorAuth('SSD App');
        if (!$tfa->verifyCode($secret, $code)) {
            return false;
        }

        $this->studentRepo->enable2FAForUser($email, $secret);
        return true;
    }

    // public function deleteStudent(string $studentId): void
    // {
    //     if (!$this->studentRepo->isStudentExists($studentId)) {
    //         throw new \Exception("Student with ID $studentId does not exist.");
    //     }
    // }

    // public function sendResetToken(string $email): void
    // {
    //     $student = $this->studentRepo->getStudentByEmail($email);
    //     if (!$student) {
    //         throw new \Exception("No student found with email $email.");
    //     }

    //     // TODO: Implement logic to send reset token
    // }

    public function updatePassword(string $email, string $oldPassword, string $newPassword): void
    {
        $student = $this->studentRepo->getStudentByEmail($email);
        if (!$student) {
            throw new \Exception("No student found with email $email.");
        }

        if (!password_verify($oldPassword, $student->getPassword())) {
            throw new \Exception("Old password is incorrect.");
        }

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

    public function checkStudentExist(string $identifier): bool
    {
        return $this->studentRepo->isStudentExists($identifier);
    }

    public function sendForgotPasswordOtp(string $email): void
    {
        $student = $this->studentRepo->getStudentByEmail($email);
        if (!$student) {
            throw new \Exception("No student found with email $email.");
        }

        $otp = strval(random_int(100000, 999999));
        $expiry = time() + 600;

        SessionManager::setOTP($otp, $expiry);
        SessionManager::setForgotPasswordEmail($email);

        $this->sendOtpEmail($email, $otp);
    }

    public function resendOtp(string $email): void
    {
        $registration = SessionManager::getRegistration();
        if (!$registration) {
            throw new \Exception("No pending registration found for this session.");
        }

        if ($registration['email'] !== $email) {
            throw new \Exception("Email mismatch for pending registration.");
        }

        $otp = strval(random_int(100000, 999999));
        $otpExpiry = (new \DateTime('+10 minutes'))->getTimestamp();

        SessionManager::setOTP($otp, $otpExpiry);

        $this->sendOtpEmail($email, $otp);
    }
}
