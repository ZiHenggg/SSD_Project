<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../src/bootstrap.php';

$title = "Register";

// Get current step
$step = $_SESSION['register_step'] ?? 'form';
$message = $_SESSION['register_message'] ?? '';
$isSuccessModal = $_SESSION['register_success'] ?? false;

// Clear once shown
unset($_SESSION['register_step'], $_SESSION['register_message'], $_SESSION['register_success']);

ob_start();
?>

<div class="w-50 m-auto">
    <h2 class="mb-4">Register</h2>

    <?php if ($message && !$isSuccessModal): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($step === 'form'): ?>
        <!-- Register Form -->
        <form method="POST" action="process_register.php" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="studentId" class="form-label">Student ID (7 digits)</label>
                <input type="text" id="studentId" name="studentId" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="studentName" class="form-label">Name</label>
                <input type="text" id="studentName" name="studentName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    <?php elseif ($step === 'otp'): ?>
        <!-- OTP Form -->
        <form method="POST" action="process_register.php">
            <div class="mb-3">
                <label for="otp" class="form-label">Enter OTP sent to your email</label>
                <input type="text" id="otp" name="otp" class="form-control" required maxlength="6">
            </div>
            <button type="submit" class="btn btn-success">Verify & Create Account</button>
        </form>
    <?php endif; ?>

    <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>

    <!-- Modal -->
    <div class="modal fade" id="registerSuccessModal" tabindex="-1" aria-labelledby="registerSuccessLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registration Successful</h5>
                </div>
                <div class="modal-body">
                    Your account has been created successfully!
                    <br>You will be redirected to login in <span id="redirect-timer">10</span> seconds.
                </div>
                <div class="modal-footer">
                    <a href="register.php" class="btn btn-secondary">Stay</a>
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isSuccessModal): ?>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('registerSuccessModal'));
        modal.show();

        let t = 10;
        const timer = document.getElementById('redirect-timer');
        const interval = setInterval(() => {
            t--; timer.textContent = t;
            if (t <= 0) {
                clearInterval(interval);
                window.location.href = "login.php";
            }
        }, 1000);
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
include '_layout.php';
