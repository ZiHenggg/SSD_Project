<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../src/CsrfManager.php';


use App\SessionManager;

$title = "Login";
ob_start();
?>
<div class="w-50 m-auto change-pw-wrapper">

    <div class="card shadow p-4">
        <h2 class="mb-4 text-center">Change Password</h2>
        <?php
        $error = SessionManager::getChangePWError();
        if ($error) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
            SessionManager::setChangePWError(null); // Clear after showing
        }?>

        <form method="post" action="process_password_update.php">
            
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CsrfManager::generateToken()); ?>">

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