<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use RobThree\Auth\TwoFactorAuth;

if (!isset($_SESSION['pending_2fa_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['pending_2fa_email'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !$user['is_2fa_enabled']) {
    echo "2FA is not set up for this account.";
    exit;
}

$tfa = new TwoFactorAuth('SSD App');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';

    if ($tfa->verifyCode($user['google2fa_secret'], $code)) {
        $_SESSION['user'] = [
            'id'    => $user['studentId'],
            'email' => $user['email'],
            'name'  => $user['name'],
        ];
        unset($_SESSION['pending_2fa_email']);
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid code. Please try again.';
    }
}

$title = "Verify 2FA";
ob_start();
?>

<div class="container w-50">
    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">Enter Your 2FA Code</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="code" class="form-label">6-digit code</label>
                <input type="text" name="code" id="code" class="form-control" required pattern="\d{6}">
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
