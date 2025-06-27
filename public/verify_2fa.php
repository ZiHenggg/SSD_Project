<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

use App\Control\StudentControl;
use App\Mapper\StudentMapper; // ✅ Use concrete class

if (!isset($_SESSION['pending_2fa_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['pending_2fa_email'];
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';

    $control = new StudentControl(new StudentMapper($pdo)); // ✅ Correct class
    $result = $control->verify2FACode($email, $code);

    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $error = $result['message'];
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
