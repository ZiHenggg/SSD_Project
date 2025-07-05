<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use App\Control\StudentControl;
use App\Mapper\StudentMapper;
use App\SessionManager;

SessionManager::start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ==== Session Management ====
$twoFA = SessionManager::get2FA();
$email = $twoFA['pending_email'] ?? null;

// Already fully logged in — block access
if ($user && !$email) {
    header("Location: dashboard.php");
    exit;
}

// Not even mid-login
if (!$email) {
    header("Location: login.php");
    exit;
}
// =====================

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';

    $control = new StudentControl(new StudentMapper($pdo));
    $result = $control->verify2FACode($email, $code);

    if ($result['success']) {
        $student = (new StudentMapper($pdo))->getStudentByEmail($email);

        SessionManager::setUser([
            'id' => $student->getStudentID(),
            'email' => $student->getEmail(),
            'name' => $student->getStudentName(),
        ]);

        // ✅ Critical: Set 2FA verified flag
        SessionManager::set('2fa_verified', true);

        // ✅ Clear 2FA temp session data
        SessionManager::set2FA(null, null);

        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $error = $result['message'];
    }
}

$title = "Verify 2FA";
ob_start();
?>
<!-- Your HTML form continues below -->
