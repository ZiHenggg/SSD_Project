<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use Predis\Client as RedisClient;

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379,
]);

$ip = $_SERVER['REMOTE_ADDR'];
$email = $_SESSION['forgot_email'] ?? ($_POST['email'] ?? null);
$emailKey = $email ? strtolower(trim($email)) : '';
$ipKey = "forgot:ip:$ip";
$otpKey = "forgot:otp_attempts:$emailKey";
$resendKey = "forgot:resend:$emailKey";

// Reset session if GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    unset(
        $_SESSION['forgot_step'],
        $_SESSION['forgot_message'],
        $_SESSION['forgot_email'],
        $_SESSION['otp'],
        $_SESSION['otp_expiry'],
        $_SESSION['forgot_started_at']
    );
    header("Location: forgot_password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: Email Submission
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $emailKey = strtolower($email);

        $ipAttempts = (int) $redis->get($ipKey);
        if ($ipAttempts >= 3) {
            $_SESSION['forgot_message'] = "Too many attempts from your IP. Please wait 10 minutes.";
            $_SESSION['forgot_step'] = 'form';
            header("Location: forgot_password.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['forgot_message'] = "Invalid email format.";
            $_SESSION['forgot_step'] = 'form';
            $redis->incr($ipKey);
            if ($redis->ttl($ipKey) <= 0) {
                $redis->expire($ipKey, 120);
            }
            header("Location: forgot_password.php");
            exit;
        }

        if (!$control->checkStudentExist($email)) {
            $_SESSION['forgot_message'] = "No account found with this email.";
            $_SESSION['forgot_step'] = 'form';
            $redis->incr($ipKey);
            if ($redis->ttl($ipKey) <= 0) {
                $redis->expire($ipKey, 120);
            }
            header("Location: forgot_password.php");
            exit;
        }

        try {
            $control->sendForgotPasswordOtp($email);
            $_SESSION['forgot_step'] = 'otp';
            $_SESSION['forgot_email'] = $email;
            $_SESSION['forgot_started_at'] = time();
            $_SESSION['forgot_message'] = "An OTP has been sent to your email.";
        } catch (Exception $e) {
            $_SESSION['forgot_message'] = "Failed to send OTP: " . $e->getMessage();
            $_SESSION['forgot_step'] = 'form';
        }

        $redis->incr($ipKey);
        if ($redis->ttl($ipKey) <= 0) {
            $redis->expire($ipKey, 120);
        }

        header("Location: forgot_password.php");
        exit;
    }

    // Step 2: Resend OTP
    if (isset($_POST['resend_otp'])) {
        if (!$emailKey) {
            $_SESSION['forgot_message'] = "Session expired. Please restart.";
            $_SESSION['forgot_step'] = 'form';
            header("Location: forgot_password.php");
            exit;
        }

        if ((int) $redis->get($resendKey) >= 3) {
            $_SESSION['forgot_message'] = "You’ve reached the resend limit. Try again in 15 minutes.";
            $_SESSION['forgot_step'] = 'otp';
            header("Location: forgot_password.php");
            exit;
        }

        try {
            $control->sendForgotPasswordOtp($emailKey);
            $redis->del($otpKey);
            $redis->incr($resendKey);
            if ($redis->ttl($resendKey) <= 0) {
                $redis->expire($resendKey, 900);
            }

            $_SESSION['forgot_message'] = "A new OTP has been sent.";
            $_SESSION['forgot_step'] = 'otp';
        } catch (Exception $e) {
            $_SESSION['forgot_message'] = "Failed to resend OTP: " . $e->getMessage();
            $_SESSION['forgot_step'] = 'otp';
        }

        header("Location: forgot_password.php");
        exit;
    }

    // Step 3: OTP Verification
    if (isset($_POST['otp'])) {
        $otpInput = trim($_POST['otp'] ?? '');

        if (!isset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['forgot_email'])) {
            $_SESSION['forgot_message'] = "Session expired. Please restart.";
            $_SESSION['forgot_step'] = 'form';
            header("Location: forgot_password.php");
            exit;
        }

        if (time() > $_SESSION['otp_expiry']) {
            unset($_SESSION['otp'], $_SESSION['otp_expiry']);
            $_SESSION['forgot_message'] = "OTP has expired. Please try again.";
            $_SESSION['forgot_step'] = 'form';
            header("Location: forgot_password.php");
            exit;
        }

        if ($_SESSION['otp'] !== $otpInput) {
            if ((int) $redis->get($otpKey) >= 5) {
                $_SESSION['forgot_message'] = "Too many incorrect OTPs. Try again in 5 minutes.";
                $_SESSION['forgot_step'] = 'otp';
                header("Location: forgot_password.php");
                exit;
            }

            $redis->incr($otpKey);
            if ($redis->ttl($otpKey) <= 0) {
                $redis->expire($otpKey, 300);
            }

            $_SESSION['forgot_message'] = "Invalid OTP.";
            $_SESSION['forgot_step'] = 'otp';
            header("Location: forgot_password.php");
            exit;
        }

        unset($_SESSION['otp'], $_SESSION['otp_expiry']);
        $redis->del($otpKey);
        $_SESSION['forgot_step'] = 'reset';
        $_SESSION['forgot_message'] = "OTP verified. Please enter your new password.";
        header("Location: forgot_password.php");
        exit;
    }

    // Step 4: Reset Password
    if (isset($_POST['new_password'])) {
        $newPassword = $_POST['new_password'] ?? '';
        $email = $_SESSION['forgot_email'] ?? null;

        if (!$email) {
            $_SESSION['forgot_message'] = "Session expired. Please restart.";
            $_SESSION['forgot_step'] = 'form';
            header("Location: forgot_password.php");
            exit;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $repo->updatePassword($email, $hashed);
        $repo->disable2FA($email);

        // ✅ Clear resend rate limit now that reset is complete
        $redis->del($resendKey);

        unset(
            $_SESSION['forgot_email'],
            $_SESSION['otp'],
            $_SESSION['otp_expiry']
        );

        $_SESSION['forgot_step'] = 'done';
        $_SESSION['forgot_message'] = "Your password has been reset successfully.";
        header("Location: forgot_password.php");
        exit;
    }
}
