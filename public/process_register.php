<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\SessionManager;

SessionManager::start();

$page = $pageControllers['studentPageController'];
$control = $pageControllers['studentControl'];
$actionController = $pageControllers['actionController'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'];

    // Step 1: Form Submission (rate-limit this)
    if (isset($_POST['studentId'])) {
        // Bypass OTP in GitHub Actions CI (Remove for production))
        if (getenv('CI') === 'true') {
            SessionManager::set('email', $_POST['email']);
            SessionManager::setRegisterStep('otp');
            SessionManager::setRegisterMessage('Mocked OTP step in CI');
            SessionManager::setRegisterMessageType('success');
            SessionManager::setRegisterSuccess(true);
            header('Location: register.php');
            exit;
        }

        if (!$actionController->onIpAction($ip, 'register')) {
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

    // Step 2: Resend OTP
    } elseif (isset($_POST['resend_otp'])) {
        $email = SessionManager::get('email');

        if (!$actionController->onIpAction($ip, 'resend_otp')) {
            SessionManager::setRegisterMessage("OTP resend limit reached. Please try again later in 10 minutes.");
            SessionManager::setRegisterMessageType('danger');
            SessionManager::setRegisterStep('otp');
            header("Location: register.php");
            exit;
        }

        $control->resendOtp($email);
        $actionController->resetIncorrectOtpCount($ip, 'ip');
        SessionManager::setRegisterMessage("A new OTP has been sent to your email.");
        SessionManager::setRegisterMessageType('success');
        SessionManager::setRegisterStep('otp');
        header("Location: register.php");
        exit;

    // Step 3: OTP Verification
    } elseif (isset($_POST['otp'])) {
        $otp = $_POST['otp'] ?? '';
        $email = SessionManager::get('email');
        if (!$actionController->onIpAction($ip, 'incorrect_otp')) {
            SessionManager::setRegisterMessage("Too many failed OTP attempts. Please try again later in 5 minutes.");
            SessionManager::setRegisterMessageType('danger');
            SessionManager::setRegisterStep('otp');
            header("Location: register.php");
            exit;
        }

        $result = $control->verifyOtp($otp);
        if ($result['success']) {
            SessionManager::resetRegisterFlow();
            SessionManager::setRegisterSuccess(true);

        } else {
            SessionManager::setRegisterMessage($result['message']);
            SessionManager::setRegisterMessageType('danger');
            SessionManager::setRegisterStep('otp');
        }
    }
}

header('Location: register.php');
exit;
