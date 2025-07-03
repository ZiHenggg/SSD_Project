<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use RobThree\Auth\TwoFactorAuth;
use App\SessionManager;

// ===== SESSION STUFF =====
SessionManager::start();

// Get current user and 2FA data from session (via SessionManager)
$twoFA = SessionManager::get2FA();
$email = $twoFA['pending_email'] ?? null;
$secret = $twoFA['secret'] ?? null;
$code = $_POST['code'] ?? '';

$title = "Confirm 2FA Setup";

// Redirect if session data is missing
if (!$email || !$secret) {
    header('Location: login.php');
    exit;
}
// ==========================



// ===== CONFIRMATION LOGIC =====
$control = new StudentControl(new StudentMapper($pdo));

if ($control->confirm2FASetup($email, $code, $secret)) {
    
    $student = $control->getStudentByEmail($email);

    SessionManager::setUser([
        'id' => $student->getStudentID(),
        'email' => $student->getEmail(),
        'name' => $student->getStudentName(),
    ]);

    // Clear 2FA session data after successful setup
    SessionManager::set('2fa_verified', true); 
    SessionManager::set2FA(null, null);

    header('Location: dashboard.php');
    exit;
}
// ===============================

ob_start();
?>

<div class="container w-50 twofa-wrapper">
    <div class="card shadow p-4 text-center">
        <h2 class="mb-3">Invalid Code</h2>
        <p>The code you entered is incorrect. Please <a href="setup_2fa.php">go back</a> and try again.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
