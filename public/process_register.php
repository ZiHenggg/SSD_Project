<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Predis\Client as RedisClient;
use App\SessionManager;

SessionManager::start();

$page = $pageControllers['studentPageController'];
$control = $pageControllers['studentControl'];

$redis = new RedisClient([
    'scheme' => 'tcp',
    'host'   => 'redis',
    'port'   => 6379,
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: Form Submission
    if (isset($_POST['studentId'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'unknown';

        $ipKey = "register_attempts:ip:" . $ip;
        $maxAttempts = 10;
        $lockoutDuration = 600;

        $ipAttempts = (int) $redis->get($ipKey);
        if ($ipAttempts >= $maxAttempts) {
            logEvent('warn', 'Registration blocked due to rate limit');
            SessionManager::setRegisterMessage("Too many registration attempts. Please try again later.");
            SessionManager::setRegisterStep('form');
            header('Location: register.php');
            exit;
        }

        $formData = [
            'studentId'    => $_POST['studentId'],
            'studentName'  => $_POST['studentName'],
            'email'        => $_POST['email'],
            'password'     => $_POST['password'], // used internally
        ];

        $sanitizedData = $formData;
        unset($sanitizedData['password']);

        $error = $page->validateStudentInput($formData);
        if ($error) {
            logEvent('warn', 'Registration failed - invalid input', [
                'error' => $error,
                'input' => $sanitizedData
            ]);
            SessionManager::setRegisterMessage($error);
            SessionManager::setRegisterStep('form');
        } elseif ($control->checkStudentExist($formData['studentId']) || $control->checkStudentExist($formData['email'])) {
            logEvent('warn', 'Registration failed - student already exists', ['input' => $sanitizedData]);
            SessionManager::setRegisterMessage("Student already exists.");
            SessionManager::setRegisterStep('form');
        } elseif (explode('@', $formData['email'])[0] !== $formData['studentId']) {
            logEvent('warn', 'Registration failed - email does not match student ID', ['input' => $sanitizedData]);
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
            logEvent('info', 'Student registered successfully', [
                'studentId' => $formData['studentId'],
                'email'     => $formData['email']
            ]);
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
            logEvent('warn', 'OTP resend blocked - too many attempts', ['email' => $email]);
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

        logEvent('info', 'OTP resent to user', ['email' => $email]);

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
            logEvent('warn', 'OTP verification blocked - too many attempts', ['email' => $email]);
            SessionManager::setRegisterMessage("Too many failed OTP attempts. Please try again later in 5 minutes.");
            SessionManager::setRegisterMessageType('danger');
            SessionManager::setRegisterStep('otp');
            header("Location: register.php");
            exit;
        }

        $result = $control->verifyOtp($otp);
        if ($result['success']) {
            logEvent('info', 'OTP verified successfully', ['email' => $email]);
            $redis->del($otpKey);
            SessionManager::resetRegisterFlow();
            SessionManager::setRegisterSuccess(true);
        } else {
            logEvent('warn', 'OTP verification failed', ['email' => $email]);
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
