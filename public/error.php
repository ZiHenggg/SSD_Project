<?php
define('STATIC_PAGE', true);
error_reporting(E_ALL);
ini_set('display_errors', 0);
$title = "Access Denied";
ob_start();
?>

<div class="w-75 m-auto text-center py-5">
    <img src="/img/professor.jpg" alt="Professor face" style="max-height: 350px;">
    <img src="/img/professor2.jpg" alt="Professor face" style="max-height: 350px;">
    <h1 class="display-4 text-danger">Access Denied!</h1>
    <p class="lead mb-4">You are not authorized to view this page or resource.</p>

    <div class="alert alert-info mx-auto w-75 text-start">
        <h5 class="fw-bold">🔒 OWASP A01:2021 – Broken Access Control</h5>
        <p class="mb-0">
            Access control enforces policy such that users cannot act outside of their intended permissions.
            This page is protected to prevent unauthorized access to sensitive or internal resources.
        </p>
    </div>

    <a href="/index.php" class="btn btn-primary mt-4">Go to Home</a>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
