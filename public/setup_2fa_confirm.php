<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use RobThree\Auth\TwoFactorAuth;
use App\SessionManager;

SessionManager::start();

$twoFA = SessionManager::get2FA();
$email = $twoFA['pending_email'] ?? null;
$secret = $twoFA['secret'] ?? null;
$code = $_POST['code'] ?? '';

$title = "Confirm 2FA Setup";
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!$email || !$secret) {
    header('Location: login.php');
    exit;
}

$control = new StudentControl(new StudentMapper($pdo));

if ($control->confirm2FASetup($email, $code, $secret)) {
    $student = $control->getStudentByEmail($email);

    SessionManager::setUser([
        'id' => $student->getStudentID(),
        'email' => $student->getEmail(),
        'name' => $student->getStudentName(),
    ]);

    // ✅ Log 2FA setup confirmation
    logEvent('info', '2FA setup confirmed', [
        'studentId' => $student->getStudentID(),
        'email' => $student->getEmail(),
        'ip' => $ip
    ]);

    // ✅ Log that user is now fully logged in via 2FA setup
    logEvent('info', 'Login complete (via 2FA setup)', [
        'studentId' => $student->getStudentID(),
        'email' => $student->getEmail(),
        'ip' => $ip
    ]);

    SessionManager::set('2fa_verified', true); 
    SessionManager::set2FA(null, null);

    header('Location: dashboard.php');
    exit;
}

// ❌ Log failed setup attempt
logEvent('warn', '2FA setup failed - invalid code', [
    'email' => $email,
    'ip' => $ip
]);

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
