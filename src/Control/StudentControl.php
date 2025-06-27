<?php
namespace App\Control;

use App\Entity\Student;
use App\Repository\StudentRepository;
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

    public function getStudentById(string $studentId): ?Student
    {
        $student = $this->studentRepo->getStudentById($studentId);
        return $student ?: null;
    }

    public function registerStudentAccount(int $studentId, string $studentName, string $email, string $password): void
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $otp = strval(random_int(100000, 999999));
        $otpExpiry = (new \DateTime('+10 minutes'))->format('Y-m-d H:i:s');

        $_SESSION['pending_registration'] = [
            'studentId' => $studentId,
            'studentName' => $studentName,
            'email' => $email,
            'password' => $hashedPassword
        ];
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = strtotime($otpExpiry);

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
        if (!isset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['pending_registration'])) {
            return ['success' => false, 'message' => 'Session expired. Please try again.'];
        }

        if (time() > $_SESSION['otp_expiry']) {
            return ['success' => false, 'message' => 'OTP expired.'];
        }

        if ($_SESSION['otp'] !== $inputOtp) {
            return ['success' => false, 'message' => 'Invalid OTP.'];
        }

        $data = $_SESSION['pending_registration'];
        $student = new Student($data['studentId'], $data['studentName'], $data['email'], $data['password']);
        $this->studentRepo->createStudentAccount($student);
        $this->studentRepo->verifyStudentEmail($student->getEmail());

        unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['pending_registration']);

        return ['success' => true, 'message' => 'Registration complete!'];
    }

    public function loginStudent(string $email, string $password): array
    {
        $student = $this->studentRepo->getStudentByEmail($email);

        if (!$student || !password_verify($password, $student->getPassword())) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        if ($student->is2FAEnabled()) {
            $_SESSION['pending_2fa_email'] = $student->getEmail();
            return ['success' => true, 'redirect' => 'verify_2fa.php'];
        }

        $_SESSION['user'] = [
            'id' => $student->getStudentId(),
            'email' => $student->getEmail(),
            'name' => $student->getStudentName()
        ];
        $_SESSION['pending_2fa_secret'] = null;

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
            $_SESSION['user'] = [
                'id'    => $student->getStudentId(),
                'email' => $student->getEmail(),
                'name'  => $student->getStudentName()
            ];
            unset($_SESSION['pending_2fa_email']);

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

    public function deleteStudent(string $studentId): void
    {
        if (!$this->studentRepo->isStudentExists($studentId)) {
            throw new \Exception("Student with ID $studentId does not exist.");
        }
    }

    public function sendResetToken(string $email): void
    {
        $student = $this->studentRepo->getStudentByEmail($email);
        if (!$student) {
            throw new \Exception("No student found with email $email.");
        }

        // TODO: Implement logic to send reset token to the student's email
    }

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
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 600;
        $_SESSION['forgot_email'] = $email;

        $this->sendOtpEmail($email, $otp);
    }

    public function resendOtp(string $email): void
    {
        if (!isset($_SESSION['pending_registration'])) {
            throw new \Exception("No pending registration found for this session.");
        }

        if ($_SESSION['pending_registration']['email'] !== $email) {
            throw new \Exception("Email mismatch for pending registration.");
        }

        $otp = strval(random_int(100000, 999999));
        $otpExpiry = (new \DateTime('+10 minutes'))->format('Y-m-d H:i:s');

        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = strtotime($otpExpiry);

        $this->sendOtpEmail($email, $otp);
    }
}
