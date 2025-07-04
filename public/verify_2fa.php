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
        $student = (new StudentMapper($pdo))->getStudentByEmail($email); // ✅ add this

        SessionManager::setUser([
            'id' => $student->getStudentID(),
            'email' => $student->getEmail(),
            'name' => $student->getStudentName(),
        ]);

        // Clear 2FA session data after successful setup
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

<div class="container w-50 twofa-wrapper">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">Enter Your 2FA Code</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <input type="text" name="code" id="code" class="form-control" placeholder="Enter Code" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
