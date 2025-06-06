<?php
require_once __DIR__ . '/../src/bootstrap.php';

if (isset($_SESSION['user'])) {
    // Redirect to dashboard or home
    header("Location: dashboard.php");
    exit();
}

$title = "Register";
$message = '';
$successText = "Account created successfully!";

$isSuccessModal = false;
if (isset($_SESSION['register_result'])) {
    $message = $_SESSION['register_result'];
    unset($_SESSION['register_result']);

    if (str_starts_with($message, $successText)) {
        $isSuccessModal = true;
    }
}

ob_start();
?>

<div class="w-50 m-auto">
    <h2 class="mb-4">Register</h2>

    <?php
    $isSuccess = str_starts_with($message ?? '', $successText);
    if ($message && !$isSuccess): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form action="process_register.php" method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="studentId" class="form-label">Student ID (7 digits)</label>
            <input type="text" id="studentId" name="studentId" class="form-control" required autocomplete="off">
        </div>

        <div class="mb-3">
            <label for="studentName" class="form-label">Name</label>
            <input type="text" id="studentName" name="studentName" class="form-control" required autocomplete="name">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required autocomplete="email">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>

    <!-- Success Modal -->
    <div class="modal fade" id="registerSuccessModal" tabindex="-1" aria-labelledby="registerSuccessLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerSuccessLabel">Registration Successful</h5>
                </div>
                <div class="modal-body">
                    Your account has been created successfully!
                    <br>You will be redirected to login in <span id="redirect-timer">10</span> seconds.<br>
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
        window.addEventListener('DOMContentLoaded', function () {
            const modal = new bootstrap.Modal(document.getElementById('registerSuccessModal'));
            modal.show();

            let countdown = 10;
            const timerElement = document.getElementById('redirect-timer');
            const countdownInterval = setInterval(() => {
                countdown--;
                timerElement.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = "login.php";
                }
            }, 1000);
        });

        // // Redirect to login after 10 seconds (10000 ms)
        // setTimeout(() => {
        //     window.location.href = "login.php";
        // }, 10000);
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
include '_layout.php';
?>