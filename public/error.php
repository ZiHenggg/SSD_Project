<?php
define('STATIC_PAGE', true);
error_reporting(E_ALL);
ini_set('display_errors', 0);

$title = "Error";

ob_start();
?>

<div class="w-75 m-auto text-center py-5">
    <img src="/img/professor.jpg" alt="Professor face" style="max-height: 300px;">

    <h1 class="display-4 text-danger mt-4">Oops! Something's not right</h1>
    <p class="lead mb-4">
        Either you're trying to access a page or resource you're not authorized to view,<br>
        or something unexpected went wrong on our end.
    </p>

    <div class="alert alert-warning mx-auto w-75 text-start">
        <h5 class="fw-bold">🔒 OWASP A01:2021 – Broken Access Control</h5>
        <p class="mb-0">
            Proper access control ensures users cannot act outside their intended permissions.
            This page is protected to prevent exposure of restricted areas or actions.
        </p>
    </div>

    <a href="/index.php" class="btn btn-primary mt-4">Go to Home</a>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
