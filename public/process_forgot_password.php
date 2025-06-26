<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/bootstrap.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);

//User submits email
if (isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_message'] = "Invalid email format.";
        $_SESSION['forgot_step'] = 'form';

    } elseif (!$control->checkStudentExist($email)) {
        $_SESSION['forgot_message'] = "No account found with this email.";
        $_SESSION['forgot_step'] = 'form';

    } else {
        try {
            $control->sendForgotPasswordOtp($email);
            $_SESSION['forgot_step'] = 'otp';
            $_SESSION['forgot_message'] = "An OTP has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['forgot_step'] = 'form';
            $_SESSION['forgot_message'] = "Failed to send OTP: " . $e->getMessage();
        }
    }

//User submits OTP
} elseif (isset($_POST['otp'])) {
    $otpInput = $_POST['otp'] ?? '';

    if (!isset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['forgot_email'])) {
        $_SESSION['forgot_message'] = "Session expired. Please restart.";
        $_SESSION['forgot_step'] = 'form';

    } elseif (time() > $_SESSION['otp_expiry']) {
        unset($_SESSION['otp'], $_SESSION['otp_expiry']);
        $_SESSION['forgot_message'] = "OTP has expired. Please try again.";
        $_SESSION['forgot_step'] = 'form';

    } elseif ($_SESSION['otp'] !== $otpInput) {
        $_SESSION['forgot_message'] = "Invalid OTP.";
        $_SESSION['forgot_step'] = 'otp';

    } else {
        unset($_SESSION['otp'], $_SESSION['otp_expiry']);
        $_SESSION['forgot_step'] = 'reset';
        $_SESSION['forgot_message'] = "OTP verified. Please enter your new password.";
    }

//User submits new password
} elseif (isset($_POST['new_password'])) {
    $newPassword = $_POST['new_password'] ?? '';
    $email = $_SESSION['forgot_email'] ?? null;

    if (!$email) {
        $_SESSION['forgot_message'] = "Session expired. Please restart.";
        $_SESSION['forgot_step'] = 'form';

    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $repo->updatePassword($email, $hashed);

        $repo->disable2FA($email);

        unset($_SESSION['forgot_email']);
        $_SESSION['forgot_step'] = 'done';
        $_SESSION['forgot_message'] = "Your password has been reset successfully.";
    }
}

header("Location: forgot_password.php");
exit;
