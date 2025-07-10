<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/CsrfManager.php';

use App\Mapper\StudentMapper;
use App\SessionManager;

SessionManager::start();

$repo = new StudentMapper($pdo);
$control = $pageControllers['studentControl'];
$pageController = $pageControllers['studentPageController'];
$actionController = $pageControllers['actionController'];

$ip = $_SERVER['REMOTE_ADDR'];
$email = SessionManager::getForgotPasswordEmail() ?? ($_POST['email'] ?? null);
$emailKey = $email ? strtolower(trim($email)) : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    SessionManager::resetForgotFlow();
    header("Location: forgot_password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate CSRF token
    if (!CsrfManager::validateToken($_POST['csrf_token'] ?? '')) {
        SessionManager::destroy();
        header('Location: error.php');
        exit;
    }

    // Step 1: Email Submission
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $emailKey = strtolower($email);

        if (!$actionController->onIpAction($ip, 'forgot_password')) {
            SessionManager::setForgotMessage("Too many attempts from your IP. Please wait 10 minutes.");
            SessionManager::setForgotStep('form');
            header("Location: forgot_password.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            logEvent('warn', 'Invalid email format');
            SessionManager::setForgotMessage("Invalid email format.");
            SessionManager::setForgotStep('form');
            header("Location: forgot_password.php");
            exit;
        }

        if (!$control->checkStudentExist($email)) {
            logEvent('warn', 'Forgot password email not found', ['email' => $email]);
            SessionManager::setForgotMessage("No account found with this email.");
            SessionManager::setForgotStep('form');
            header("Location: forgot_password.php");
            exit;
        }

        try {
            $actionController->onIpAction($ip, 'resend_otp');
            $control->sendForgotPasswordOtp($email);
            logEvent('info', 'Forgot password OTP sent', ['email' => $email]);
            SessionManager::setForgotStep('otp');
            SessionManager::setForgotPasswordEmail($email);
            SessionManager::setForgotStartedAt(time());
            SessionManager::setForgotMessage("An OTP has been sent to your email.");
        } catch (Exception $e) {
            logEvent('error', 'Failed to send forgot password OTP', ['email' => $email, 'error' => $e->getMessage()]);
            SessionManager::setForgotMessage("Failed to send OTP: " . $e->getMessage());
            SessionManager::setForgotStep('form');
        }
        header("Location: forgot_password.php");
        exit;
    }

    // Step 2: Resend OTP
    if (isset($_POST['resend_otp'])) {
        if (!$emailKey) {
            SessionManager::setForgotMessage("Session expired. Please restart.");
            SessionManager::setForgotStep('form');
            header("Location: forgot_password.php");
            exit;
        }

        if (!$actionController->onIpAction($ip, 'resend_otp')) {
            SessionManager::setForgotMessage("You’ve reached the resend limit. Try again in 10 minutes.");
            SessionManager::setForgotStep('otp');
            header("Location: forgot_password.php");
            exit;
        }

        try {
            $control->sendForgotPasswordOtp($emailKey);
            $actionController->resetIncorrectOtpCount($ip, 'ip');

            logEvent('info', 'OTP resent successfully', ['email' => $emailKey]);

            SessionManager::setForgotMessage("A new OTP has been sent.");
            SessionManager::setForgotStep('otp');
        } catch (Exception $e) {
            logEvent('error', 'Failed to resend OTP', ['email' => $emailKey, 'error' => $e->getMessage()]);
            SessionManager::setForgotMessage("Failed to resend OTP: " . $e->getMessage());
            SessionManager::setForgotStep('otp');
        }

        header("Location: forgot_password.php");
        exit;
    }

    // Step 3: OTP Verification
    if (isset($_POST['otp'])) {
        $otpInput = trim($_POST['otp'] ?? '');
        $otpData = SessionManager::getOTP();
        $email = SessionManager::getForgotPasswordEmail();

        if (!$otpData || !$email) {
            SessionManager::setForgotMessage("Session expired. Please restart.");
            SessionManager::setForgotStep('form');
            header("Location: forgot_password.php");
            exit;
        }

        if (time() > $otpData['expiry']) {
            logEvent('warn', 'OTP expired during verification', ['email' => $email]);
            SessionManager::setOTP('', 0);
            SessionManager::setForgotMessage("OTP has expired. Please try again.");
            SessionManager::setForgotStep('form');
            header("Location: forgot_password.php");
            exit;
        }

        if ($otpData['code'] !== $otpInput) {
            if (!$actionController->onIpAction($ip, 'incorrect_otp')) {
                SessionManager::setForgotMessage("Too many incorrect OTPs. Try again in 10 minutes.");
                SessionManager::setForgotStep('otp');
                header("Location: forgot_password.php");
                exit;
            }

            SessionManager::setForgotMessage("Invalid OTP.");
            SessionManager::setForgotStep('otp');
            header("Location: forgot_password.php");
            exit;
        }

        SessionManager::setForgotStep('reset');
        SessionManager::setForgotMessage("OTP verified. Please enter your new password.");
        header("Location: forgot_password.php");
        exit;
    }

    // Step 4: Reset Password
    if (isset($_POST['new_password'])) {
        $newPassword = $_POST['new_password'] ?? '';
        $email = SessionManager::getForgotPasswordEmail();

        if (!$email) {
            SessionManager::setForgotMessage("Session expired. Please restart.");
            SessionManager::setForgotStep('form');
            header("Location: forgot_password.php");
            exit;
        }

        $error = $pageController->validatePassword($newPassword);
        if ($error !== null) {
            logEvent('warn', 'Password reset failed validation', ['email' => $email]);
            SessionManager::setForgotMessage($error);
            SessionManager::setForgotStep('reset');
            header("Location: forgot_password.php");
            exit;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $repo->updatePassword($email, $hashed);
        $repo->disable2FA($email); // Require re-setup of 2FA on next login

        SessionManager::resetForgotFlow();
        SessionManager::setForgotStep('done');
        SessionManager::setForgotMessage("Your password has been reset successfully.");
        header("Location: forgot_password.php");
        exit;
    }
}
