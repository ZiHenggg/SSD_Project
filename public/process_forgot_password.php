<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;
use App\SessionManager;
use Predis\Client as RedisClient;

SessionManager::start();

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$pageController  =  new StudentPageController($control);
$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379,
]);

$email = SessionManager::getForgotPasswordEmail() ?? ($_POST['email'] ?? null);
$emailKey = $email ? strtolower(trim($email)) : '';
$ipKey = "forgot:ip:$emailKey";
$otpKey = "forgot:otp_attempts:$emailKey";
$resendKey = "forgot:resend:$emailKey";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    SessionManager::resetForgotFlow();
    header("Location: forgot_password.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: Email Submission
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $emailKey = strtolower($email);

        $ipAttempts = (int) $redis->get($ipKey);
        if ($ipAttempts >= 10) {
            logEvent('warn', 'Forgot password blocked: too many IP attempts');
            SessionManager::setForgotMessage("Too many attempts from your IP. Please wait 10 minutes.");
            SessionManager::setForgotStep('form');
            header("Location: forgot_password.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            logEvent('warn', 'Invalid email format');
            SessionManager::setForgotMessage("Invalid email format.");
            SessionManager::setForgotStep('form');
            $redis->incr($ipKey);
            if ($redis->ttl($ipKey) <= 0) {
                $redis->expire($ipKey, 600);
            }
            header("Location: forgot_password.php");
            exit;
        }

        if (!$control->checkStudentExist($email)) {
            logEvent('warn', 'Forgot password email not found', ['email' => $email]);
            SessionManager::setForgotMessage("No account found with this email.");
            SessionManager::setForgotStep('form');
            $redis->incr($ipKey);
            if ($redis->ttl($ipKey) <= 0) {
                $redis->expire($ipKey, 120);
            }
            header("Location: forgot_password.php");
            exit;
        }

        try {
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

        $redis->incr($ipKey);
        if ($redis->ttl($ipKey) <= 0) {
            $redis->expire($ipKey, 120);
        }

        logEvent('info', 'Forgot password email submitted', ['email' => $email]);
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

        if ((int) $redis->get($resendKey) >= 3) {
            logEvent('warn', 'OTP resend blocked - limit reached', ['email' => $emailKey]);
            SessionManager::setForgotMessage("You’ve reached the resend limit. Try again in 15 minutes.");
            SessionManager::setForgotStep('otp');
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
            if ((int) $redis->get($otpKey) >= 5) {
                logEvent('warn', 'OTP blocked after too many failures', ['email' => $email]);
                SessionManager::setForgotMessage("Too many incorrect OTPs. Try again in 5 minutes.");
                SessionManager::setForgotStep('otp');
                header("Location: forgot_password.php");
                exit;
            }

            $redis->incr($otpKey);
            if ($redis->ttl($otpKey) <= 0) {
                $redis->expire($otpKey, 300);
            }

            logEvent('warn', 'OTP invalid', ['email' => $email]);
            SessionManager::setForgotMessage("Invalid OTP.");
            SessionManager::setForgotStep('otp');
            header("Location: forgot_password.php");
            exit;
        }

        logEvent('info', 'OTP verified', ['email' => $email]);
        $redis->del($otpKey);
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
        $repo->disable2FA($email);

        $redis->del($resendKey);

        logEvent('info', 'Password reset successfully', ['email' => $email]);

        SessionManager::resetForgotFlow();
        SessionManager::setForgotStep('done');
        SessionManager::setForgotMessage("Your password has been reset successfully.");
        header("Location: forgot_password.php");
        exit;
    }
}
