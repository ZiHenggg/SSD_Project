<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use RobThree\Auth\TwoFactorAuth;
use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$pageController = new StudentPageController($control);

// Check if user is logged in and has a pending 2FA email
if (!isset($_SESSION['pending_2fa_email']) || $_SESSION['2fa_context'] !== 'password_update') {
    header("Location: change_password.php");
    exit;
}

$email = $_SESSION['pending_2fa_email'];

$secret = $pageController->get2FASecretForEmail($email);

$tfa = new TwoFactorAuth('SSD App');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';

    if ($tfa->verifyCode($secret, $code)) {
        // 2FA passed — now update the password
        try {
            // Check if the session has the required data for password update
            if (
                !isset($_SESSION['pending_pw_change']['old_password']) ||
                !isset($_SESSION['pending_pw_change']['new_password'])
            ) {
                $_SESSION['change_pw_error'] = "Password update data is missing or expired.";
                header("Location: change_password.php");
                exit;
            }


            $result = $pageController->updatePassword($email, [
                'old_password' => $_SESSION['pending_pw_change']['old_password'],
                'new_password' => $_SESSION['pending_pw_change']['new_password']
            ]);

            if (!$result['success']) {
                $_SESSION['change_pw_error'] = $result['message'];
                header("Location: change_password.php");
                exit;
            }

            // Clear session data
            unset(
                $_SESSION['pending_pw_change'],
                $_SESSION['pending_2fa_email'],
                $_SESSION['2fa_context']
            );

            $_SESSION['change_pw_success'] = "Password updated successfully.";
            header('Location: dashboard.php');
            exit;
        } catch (\Exception $e) {
            $_SESSION['change_pw_error'] = $e->getMessage();
            header("Location: change_password.php");
            exit;
        }

    } else {
        $error = 'Invalid 2FA code. Please try again.';
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
