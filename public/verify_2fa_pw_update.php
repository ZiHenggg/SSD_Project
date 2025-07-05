<?php
require_once __DIR__ . '/../vendor/autoload.php';
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';


use App\Mapper\StudentMapper;
use App\SessionManager;

$repo = new StudentMapper($pdo);
$control = $pageControllers['studentControl'];
$pageController = $pageControllers['studentPageController'];

// Check if user is in 2FA password update flow
if (
    !SessionManager::has('pending_2fa_email') ||
    SessionManager::get('2fa_context') !== 'password_update'
) {
    header("Location: change_password.php");
    exit;
}

$email = SessionManager::get('pending_2fa_email');
$secret = $pageController->get2FASecretForEmail($email);
$error = '';

// When form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';

    // Use controller to verify 2FA code
    if ($pageController->verify2FACodeForPasswordUpdate($secret, $code)) {
        try {
            $pwChange = SessionManager::get('pending_pw_change');
        
            // Check required session data
            if (
                !isset($pwChange['old_password']) ||
                !isset($pwChange['new_password'])
            ) {
                SessionManager::setChangePWError("Password update data is missing or expired.");
                header("Location: change_password.php");
                exit;
            }

            $result = $pageController->updatePassword($email, [
                'old_password' => $pwChange['old_password'],
                'new_password' => $pwChange['new_password']
            ]);

            if (!$result['success']) {
                SessionManager::setChangePWError($result['message']);
                header("Location: change_password.php");
                exit;
            }

            // Clear session and redirect
            SessionManager::remove('pending_pw_change');
            SessionManager::remove('pending_2fa_email');
            SessionManager::remove('2fa_context');

            SessionManager::setSuccess("Password updated successfully.");
            header('Location: dashboard.php');
            exit;
        } catch (\Exception $e) {
            SessionManager::setChangePWError($e->getMessage());
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
