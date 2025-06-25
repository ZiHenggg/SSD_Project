<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use RobThree\Auth\TwoFactorAuth;

if (!isset($_SESSION['user']['email'], $_SESSION['pending_2fa_secret'])) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['user']['email'];
$secret = $_SESSION['pending_2fa_secret'];
$code = $_POST['code'] ?? '';

$tfa = new TwoFactorAuth('SSD App');
$title = "Confirm 2FA Setup";

if ($tfa->verifyCode($secret, $code)) {
    $stmt = $pdo->prepare("UPDATE students SET google2fa_secret = ?, is_2fa_enabled = 1 WHERE email = ?");
    $stmt->execute([$secret, $email]);

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
