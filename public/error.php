<?php
define('STATIC_PAGE', true);
error_reporting(E_ALL);
ini_set('display_errors', 0);
$title = "Access Denied";
ob_start();
?>

<div class="w-75 m-auto text-center py-5">
    <img src="/img/professor.jpg" alt="Professor face" style="max-height: 350px;">
    <img src="/img/professor2.png" alt="Professor face" style="max-height: 350px;">

    <h1 class="display-4 text-danger">Access Denied</h1>
    <p class="lead mb-4">You are not authorized to view this page or perform this action.</p>

    <div class="alert alert-warning mx-auto w-75 text-start">
        <h5 class="fw-bold">⚠️ What just happened?</h5>
        <p class="mb-1">
            If you are seeing this page, an error has occurred or your session may no longer be valid.
        </p>
        <ul class="mb-1">
            <li>Your session may have expired due to inactivity.</li>
            <li>Your request may have been invalid or unauthorized.</li>
            <li>You may have been logged out automatically for security reasons.</li>
        </ul>
        <p class="mb-0">
            For your safety, we’ve prevented access to the requested resource.
        </p>
    </div>

    <div class="alert alert-info mx-auto w-75 text-start">
        <h5 class="fw-bold">🔒 OWASP A01:2021 – Broken Access Control</h5>
        <p class="mb-0">
            Access control ensures users only perform actions within their permissions.
            This system includes protection mechanisms such as CSRF validation and session expiration enforcement.
        </p>
    </div>

    <div class="mt-4">
        <a href="/login.php" class="btn btn-primary">Return to Login</a>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
?>
