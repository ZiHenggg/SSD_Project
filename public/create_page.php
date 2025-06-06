<?php
echo "New Page Generator\n";
echo "===================\n";
$pageName = readline("Enter page file name (e.g., dashboard): ");
$pageTitle = readline("Enter page title (e.g., Dashboard): ");
$requiresAuth = readline("Require login? (yes/no): ");

$authLine = ($requiresAuth === 'yes')
    ? "require_once __DIR__ . '/../src/auth_check.php';"
    : "// No login required";

$template = <<<PHP
<?php
require_once __DIR__ . '/../src/bootstrap.php';
$authLine

\$title = "$pageTitle";
ob_start();
?>

<!-- Page-specific content starts here -->
<h1>Welcome to <?= htmlspecialchars(\$title) ?></h1>
<p>This is a placeholder content. You can replace this with your actual page content.</p>
<!-- Page-specific content ends -->

<?php
\$content = ob_get_clean();
include '_layout.php';
?>
PHP;

$filePath = __DIR__ . "/$pageName.php";

if (file_exists($filePath)) {
    echo "File '$pageName.php' already exists.\n";
    exit;
}

file_put_contents($filePath, $template);
echo "====================\n";
echo "'$pageName.php' has been created.\n";