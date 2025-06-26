<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/bootstrap.php';

$title = "Forgot Password";

$step = $_SESSION['forgot_step'] ?? 'form';
$message = $_SESSION['forgot_message'] ?? null;
unset($_SESSION['forgot_message']);

ob_start();
?>

<div class="w-50 m-auto">
    <h2 class="mb-4">Forgot Password</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($step === 'form'): ?>
        <form method="post" action="process_forgot_password.php">
            <div class="mb-3">
                <label for="email" class="form-label">Student Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Send OTP</button>
        </form>

    <?php elseif ($step === 'otp'): ?>
        <form method="post" action="process_forgot_password.php">
            <div class="mb-3">
                <label for="otp" class="form-label">Enter the OTP sent to your email</label>
                <input type="text" name="otp" id="otp" class="form-control" required pattern="\d{6}">
            </div>
            <button type="submit" class="btn btn-primary">Verify OTP</button>
        </form>

    <?php elseif ($step === 'reset'): ?>
        <form method="post" action="process_forgot_password.php">
            <div class="mb-3">
                <label for="new_password" class="form-label">Enter New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>

    <?php elseif ($step === 'done'): ?>
        <div class="alert alert-success text-center">
            Your password has been reset.<br>
            <a href="login.php" class="btn btn-primary">Go to Login</a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
