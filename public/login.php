ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\SessionManager;

SessionManager::start();

// ===== SESSION STUFF =====
// If already logged in, redirect to dashboard
if (SessionManager::getUser()) {
    header("Location: dashboard.php");
    exit();
}

$title = "Login";
ob_start();
?>

<div class="w-50 m-auto login-wrapper">
    <h2 class="mb-4">Login</h2>

    <?php
    // ===== DISPLAY LOGIN ERROR IF SET =====
    $error = SessionManager::getLoginError();
    if ($error) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
        SessionManager::setLoginError(null); // Clear after showing
    }
    ?>

    <form method="post" action="process_login.php" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">Student Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>

        <p class="mt-3">Forgot Password? <a href="forgot_password.php">Reset it here</a></p>
    </form>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
