<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;

if (!isset($_SESSION['user']['email'], $_SESSION['pending_2fa_secret'])) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['user']['email'];
$secret = $_SESSION['pending_2fa_secret'];
$code = $_POST['code'] ?? '';
$title = "Confirm 2FA Setup";

// ✅ Use controller to handle the logic
$control = new StudentControl(new StudentMapper($pdo));

if ($control->confirm2FASetup($email, $code, $secret)) {
    unset($_SESSION['pending_2fa_secret']);
    header('Location: dashboard.php');
    exit;
}

ob_start();
?>

<div class="container w-50">
    <div class="card shadow p-4 text-center">
        <h2 class="mb-3">Invalid Code</h2>
        <p>The code you entered is incorrect. Please <a href="setup_2fa.php">go back</a> and try again.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
