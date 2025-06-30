<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

$title = "Login";
ob_start();
?>
<div class="w-50 m-auto change-pw-wrapper">

    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">Change Password</h2>
        <?php if (!empty($_SESSION['change_pw_error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['change_pw_error']) ?></div>
            <?php unset($_SESSION['change_pw_error']); endif; ?>
        <form method="post" action="process_password_update.php">
            <div class="mb-3">
                <label for="old_password" class="form-label">Current Password</label>
                <input type="password" name="old_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Password</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include '_layout.php';
?>