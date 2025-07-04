<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Include Predis

use Predis\Client as RedisClient;
use App\SessionManager;

SessionManager::start();

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
            SessionManager::set('email', $_POST['email']);
            SessionManager::setRegisterStep('otp');
            SessionManager::setRegisterMessage('Mocked OTP step in CI');
            SessionManager::setRegisterMessageType('success');
            SessionManager::resetRegisterFlow();
            SessionManager::setRegisterSuccess(true);
            header('Location: register.php');
            exit;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $ipKey = "register_attempts:ip:" . $ip;
        $maxAttempts = 10;
        $lockoutDuration = 600; // 10 minutes

        $ipAttempts = (int) $redis->get($ipKey);
        if ($ipAttempts >= $maxAttempts) {
            SessionManager::setRegisterMessage("Too many registration attempts. Please try again later.");
            SessionManager::setRegisterStep('form');
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
            SessionManager::setRegisterMessage($error);
            SessionManager::setRegisterStep('form');
        } elseif ($control->checkStudentExist($formData['studentId']) || $control->checkStudentExist($formData['email'])) {
            SessionManager::setRegisterMessage("Student already exists.");
            SessionManager::setRegisterStep('form');
        } elseif (explode('@', $formData['email'])[0] !== $formData['studentId']) {
            SessionManager::setRegisterMessage("Email must begin with your Student ID.");
            SessionManager::setRegisterStep('form');
        } else {
            SessionManager::set('email', $formData['email']);
            $control->registerStudentAccount(
                (int)$formData['studentId'],
                $formData['studentName'],
                $formData['email'],
                $formData['password']
            );
            SessionManager::setRegisterMessage("OTP has been sent to your email.");
            SessionManager::setRegisterMessageType('success');
            SessionManager::setRegisterStep('otp');
        }

        $redis->incr($ipKey);
        if ($redis->ttl($ipKey) <= 0) {
            $redis->expire($ipKey, $lockoutDuration);
        }

    // Step 2: Resend OTP
    } elseif (isset($_POST['resend_otp'])) {
        $email = SessionManager::get('email');
        $resendKey = "resend_otp:" . $email;
        $maxResends = 3;
        $resendTTL = 900;

        $resends = (int) $redis->get($resendKey);
        if ($resends >= $maxResends) {
            SessionManager::setRegisterMessage("OTP resend limit reached. Please try again later in 15 minutes.");
            SessionManager::setRegisterMessageType('danger');
            SessionManager::setRegisterStep('otp');
            header("Location: register.php");
            exit;
        }

        $control->resendOtp($email);
        $redis->del("otp_attempts:" . $email);
        $redis->incr($resendKey);
        if ($redis->ttl($resendKey) <= 0) {
            $redis->expire($resendKey, $resendTTL);
        }

        SessionManager::setRegisterMessage("A new OTP has been sent to your email.");
        SessionManager::setRegisterMessageType('success');
        SessionManager::setRegisterStep('otp');
        header("Location: register.php");
        exit;

    // Step 3: OTP Verification
    } elseif (isset($_POST['otp'])) {
        $otp = $_POST['otp'] ?? '';
        $email = SessionManager::get('email');
        $otpKey = "otp_attempts:" . $email;
        $maxOtpAttempts = 5;
        $otpLockout = 300;

        $otpAttempts = (int) $redis->get($otpKey);
        if ($otpAttempts >= $maxOtpAttempts) {
            SessionManager::setRegisterMessage("Too many failed OTP attempts. Please try again later in 5 minutes.");
            SessionManager::setRegisterMessageType('danger');
            SessionManager::setRegisterStep('otp');
            header("Location: register.php");
            exit;
        }

        $result = $control->verifyOtp($otp);
        if ($result['success']) {
            $redis->del($otpKey);
            SessionManager::resetRegisterFlow();
            SessionManager::setRegisterSuccess(true);

        } else {
            $redis->incr($otpKey);
            if ($redis->ttl($otpKey) <= 0) {
                $redis->expire($otpKey, $otpLockout);
            }
            SessionManager::setRegisterMessage($result['message']);
            SessionManager::setRegisterMessageType('danger');
            SessionManager::setRegisterStep('otp');
        }
    }
}

header('Location: register.php');
exit;
