<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php'; // Optional: Only include on pages that require login

$title = "Change Here";

ob_start();
?>

<!-- Page-specific content starts here -->
<div class="mx-5">
    <h2>Welcome to <?= htmlspecialchars($title) ?></h2>
    <p>This is a placeholder content. You can replace this with your actual page content.</p>
</div>
<!-- Page-specific content ends -->

<?php
$content = ob_get_clean();
include '_layout.php';
?>