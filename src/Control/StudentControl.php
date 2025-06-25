<?php
namespace App\Control;

use App\Entity\Student;
use App\Repository\StudentRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class StudentControl
{
    private StudentRepository $studentRepo;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepo = $studentRepo;
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

    private function sendOtpEmail(string $to, string $otp): void
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'groupmatesxyz@gmail.com';
            $mail->Password = 'eiqbpffjricorpvi';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('groupmatesxyz@gmail.com', 'SSD App');
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

        // ✅ Mark email as verified
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

    public function checkStudentExist(string $identifier): bool
    {
        return $this->studentRepo->isStudentExists($identifier);
    }
}
