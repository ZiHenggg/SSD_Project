<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
$user = SessionManager::getUser();

// Already fully logged in and passed 2FA — redirect to dashboard
if ($user && SessionManager::get('2fa_verified')) {
    header("Location: dashboard.php");
    exit;
}

// Not even mid-login — kick to login page
if (!$email) {
    header("Location: login.php");
    exit;
}
// ============================

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

        SessionManager::set('2fa_verified', true);
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container w-50 mt-5">
        <div class="card shadow p-4">
            <h2 class="mb-4 text-center">Enter Your 2FA Code</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="code" class="form-label">Authentication Code</label>
                    <input type="text" name="code" id="code" class="form-control" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify</button>
            </form>
        </div>
    </div>
</body>
</html>
