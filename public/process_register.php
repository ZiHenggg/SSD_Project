<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Include Predis

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;
use Predis\Client as RedisClient;

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$page = new StudentPageController($control);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: Form Submission (rate-limit this)
    if (isset($_POST['studentId'])) {

        // ---- Redis rate-limiting logic ----
        $redis = new RedisClient([
            'scheme' => 'tcp',
            'host' => 'redis',
            'port' => 6379,
        ]);

        $ip = $_SERVER['REMOTE_ADDR'];
        $ipKey = "register_attempts:ip:" . $ip;
        $maxAttempts = 3;
        $lockoutDuration = 120; // 10 minutes

        $ipAttempts = (int) $redis->get($ipKey);
        if ($ipAttempts >= $maxAttempts) {
            $_SESSION['register_message'] = "Too many registration attempts. Please try again later.";
            $_SESSION['register_step'] = 'form';
            header('Location: register.php');
            exit;
        }
        // -----------------------------------

        $formData = [
            'studentId' => $_POST['studentId'],
            'studentName' => $_POST['studentName'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
        ];

        // Validate input
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
            // All good – store in session and send OTP
            $control->registerStudentAccount(
                (int)$formData['studentId'],
                $formData['studentName'],
                $formData['email'],
                $formData['password']
            );

            $_SESSION['register_message'] = "OTP has been sent to your email.";
            $_SESSION['register_step'] = 'otp';
        }

        // Record the attempt regardless of outcome
        $redis->incr($ipKey);
        if ($redis->ttl($ipKey) <= 0) {
            $redis->expire($ipKey, $lockoutDuration);
        }

    // Step 2: OTP Verification (not rate-limited)
    } elseif (isset($_POST['otp'])) {
        $otp = $_POST['otp'] ?? '';

        $result = $control->verifyOtp($otp);
        if ($result['success']) {
            $_SESSION['register_message'] = $result['message'];
            $_SESSION['register_success'] = true;
            $_SESSION['register_step'] = 'done';
        } else {
            $_SESSION['register_message'] = $result['message'];
            $_SESSION['register_step'] = 'otp';
        }
    }
}

header('Location: register.php');
exit;
