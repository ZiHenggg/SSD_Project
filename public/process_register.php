<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Include Predis

use Predis\Client as RedisClient;

$page = $pageControllers['studentPageController'];
$control = $pageControllers['studentControl'];

$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379,
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: Form Submission (rate-limit this)
    if (isset($_POST['studentId'])) {
        // Bypass Redis/OTP in GitHub Actions CI (Remove for production))
        if (getenv('CI') === 'true') {
            $_SESSION['email'] = $_POST['email'];
            $_SESSION['register_step'] = 'otp';
            $_SESSION['register_message'] = 'Mocked OTP step in CI';
            $_SESSION['register_message_type'] = 'success';
            header('Location: register.php');
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $ipKey = "register_attempts:ip:" . $ip;
        $maxAttempts = 10;
        $lockoutDuration = 600; // 10 minutes

        $ipAttempts = (int) $redis->get($ipKey);
        if ($ipAttempts >= $maxAttempts) {
            $_SESSION['register_message'] = "Too many registration attempts. Please try again later.";
            $_SESSION['register_step'] = 'form';
            header('Location: register.php');
            exit;
        }

        $formData = [
            'studentId' => $_POST['studentId'],
            'studentName' => $_POST['studentName'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
        ];

        $error = $page->validateStudentInput($formData);
        if ($error) {
            $_SESSION['register_message'] = $error;
            $_SESSION['register_step'] = 'form';
        } elseif ($control->checkStudentExist($formData['studentId']) || $control->checkStudentExist($formData['email'])) {
            $_SESSION['register_message'] = "Student already exists.";
            $_SESSION['register_step'] = 'form';
        } elseif (explode('@', $formData['email'])[0] !== $formData['studentId']) {
            $_SESSION['register_message'] = "Email must begin with your Student ID.";
            $_SESSION['register_step'] = 'form';
        } else {
            $_SESSION['email'] = $formData['email'];
            $control->registerStudentAccount(
                (int)$formData['studentId'],
                $formData['studentName'],
                $formData['email'],
                $formData['password']
            );
            $_SESSION['register_message'] = "OTP has been sent to your email.";
            $_SESSION['register_message_type'] = 'success';
            $_SESSION['register_step'] = 'otp';
        }

        $redis->incr($ipKey);
        if ($redis->ttl($ipKey) <= 0) {
            $redis->expire($ipKey, $lockoutDuration);
        }

    // Step 2: Resend OTP
    } elseif (isset($_POST['resend_otp'])) {
        $resendKey = "resend_otp:" . $_SESSION['email'];
        $maxResends = 3;
        $resendTTL = 900;

        $resends = (int) $redis->get($resendKey);
        if ($resends >= $maxResends) {
            $_SESSION['register_message'] = "OTP resend limit reached. Please try again later in 15 minutes.";
            $_SESSION['register_message_type'] = 'danger';
            $_SESSION['register_step'] = 'otp';
            header("Location: register.php");
            exit;
        }

        $control->resendOtp($_SESSION['email']);
        $redis->del("otp_attempts:" . $_SESSION['email']);
        $redis->incr($resendKey);
        if ($redis->ttl($resendKey) <= 0) {
            $redis->expire($resendKey, $resendTTL);
        }

        $_SESSION['register_message'] = "A new OTP has been sent to your email.";
        $_SESSION['register_message_type'] = 'success';
        $_SESSION['register_step'] = 'otp';
        header("Location: register.php");
        exit;

    // Step 3: OTP Verification
    } elseif (isset($_POST['otp'])) {
        $otp = $_POST['otp'] ?? '';
        $otpKey = "otp_attempts:" . $_SESSION['email'];
        $maxOtpAttempts = 5;
        $otpLockout = 300;

        $otpAttempts = (int) $redis->get($otpKey);
        if ($otpAttempts >= $maxOtpAttempts) {
            $_SESSION['register_message'] = "Too many failed OTP attempts. Please try again later in 5 minutes.";
            $_SESSION['register_message_type'] = 'danger';
            $_SESSION['register_step'] = 'otp';
            header("Location: register.php");
            exit;
        }

        $result = $control->verifyOtp($otp);
        if ($result['success']) {
            $redis->del($otpKey);
            $_SESSION['register_message'] = $result['message'];
            $_SESSION['register_success'] = true;
            $_SESSION['register_step'] = 'done';
        } else {
            $redis->incr($otpKey);
            if ($redis->ttl($otpKey) <= 0) {
                $redis->expire($otpKey, $otpLockout);
            }
            $_SESSION['register_message'] = $result['message'];
            $_SESSION['register_message_type'] = 'danger';
            $_SESSION['register_step'] = 'otp';
        }
    }
}

header('Location: register.php');
exit;
