<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/CsrfManager.php';

use App\SessionManager;

//SessionManager::start();

// If already logged in, redirect to dashboard
if (SessionManager::getUser()) {
    header("Location: dashboard.php");
    exit();
}

$title = "Forgot Password";

// Timeout duration (in seconds)
$timeout = 600; // 10 minutes

// Handle timeout expiration
if (SessionManager::isForgotFlowExpired($timeout)) {
    SessionManager::resetForgotFlow();
    SessionManager::setForgotMessage("Your session has expired. Please start over.");
    header("Location: forgot_password.php");
    exit;
}


// Refresh timestamp
SessionManager::set('forgot_last_active', time());

// Determine current step
$step = SessionManager::getForgotStep() ?? 'form';
$message = SessionManager::getForgotMessage();

// Reset session state if not in OTP, reset, or done step
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !in_array($step, ['otp', 'reset', 'done'])) {
    if (!$message) {
        SessionManager::resetForgotFlow();
        $step = 'form';
    }
}

// Clear message after showing (except for final step)
if ($message && $step !== 'done') {
    SessionManager::setForgotMessage('');
}

ob_start();
?>

<div class="w-50 m-auto forgot-wrapper">
    <h2 class="mb-4">Forgot Password</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($step === 'form'): ?>
        <form method="post" action="process_forgot_password.php">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CsrfManager::generateToken()); ?>">

            <div class="mb-3">
                <label for="email" class="form-label">Student Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary">Send OTP</button>
            </div>
        </form>

    <?php elseif ($step === 'otp'): ?>
        <form method="post" action="process_forgot_password.php" class="mb-3">
            <label for="otp" class="form-label">Enter the OTP sent to your email</label>
            <input type="text" name="otp" id="otp" class="form-control mb-3" required>

            <div class="d-flex justify-content-start gap-2">
                <button type="submit" class="btn btn-success">Verify</button>
        </form>

        <form method="post" action="process_forgot_password.php">
            <input type="hidden" name="resend_otp" value="1">
            <button type="submit" class="btn btn-danger">Resend OTP</button>
        </form>
            </div>

    <?php elseif ($step === 'reset'): ?>
        <form method="post" action="process_forgot_password.php">
            <div class="mb-3">
                <label for="new_password" class="form-label">Enter New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>

    <?php elseif ($step === 'done'): ?>
        <div class="alert alert-success text-center">
            Your password has been reset.<br>
            <a href="login.php" class="btn btn-primary mt-3">Go to Login</a>
        </div>
        <?php
        // Final cleanup
        SessionManager::resetForgotFlow();
        ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
