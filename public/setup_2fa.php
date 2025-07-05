<?php
require_once __DIR__ . '/../vendor/autoload.php';
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';

use RobThree\Auth\TwoFactorAuth;
use App\SessionManager;


// ===== SESSION STUFF =====
SessionManager::start();

$twoFA = SessionManager::get2FA();
$email = $twoFA['pending_email'] ?? null;
$secret = $twoFA['secret'] ?? null;

if (!$email) {
    header('Location: login.php');
    exit;
}
// ==========================

// Check if already has 2FA
$studentController = $pageControllers['studentPageController'];
$is2FAEnabled = $studentController->is2FAEnabled($email);

if ($is2FAEnabled) {
    header("Location: verify_2fa.php");
    exit;
}

// Generate 2FA secret + QR code
$tfa = new TwoFactorAuth('SSD App');
$secret = $tfa->createSecret();
SessionManager::set2FA(pendingEmail: $email, secret: $secret);
$qrCodeUrl = $tfa->getQRCodeImageAsDataUri($email, $secret);

$title = "Set Up 2FA";
ob_start();
?>

<div class="container w-50 twofa-wrapper">
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
