<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use RobThree\Auth\TwoFactorAuth;

// Check if user is logged in
if (!isset($_SESSION['user']['email'])) {
    header('Location: login.php');
    exit;
}

$email = $_SESSION['user']['email'];

// Check if already has 2FA
$stmt = $pdo->prepare("SELECT is_2fa_enabled FROM students WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['is_2fa_enabled']) {
    header("Location: verify_2fa.php");
    exit;
}

// Create secret + QR code
$tfa = new TwoFactorAuth('SSD App');
$secret = $tfa->createSecret();
$_SESSION['pending_2fa_secret'] = $secret;
$qrCodeUrl = $tfa->getQRCodeImageAsDataUri($email, $secret);

$title = "Set Up 2FA";
ob_start();
?>

<div class="container w-50">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">Set Up Two-Factor Authentication</h2>
        <p>Scan the QR code below using your <strong>Google Authenticator</strong> app, then enter the 6-digit code to confirm.</p>
        <div class="text-center my-3">
            <img src="<?= $qrCodeUrl ?>" alt="QR Code" class="img-fluid" style="max-width: 250px;">
        </div>
        <form method="post" action="setup_2fa_confirm.php">
            <div class="mb-3">
                <label for="code" class="form-label">Enter 6-digit code</label>
                <input type="text" name="code" id="code" class="form-control" pattern="\d{6}" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify and Enable 2FA</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
