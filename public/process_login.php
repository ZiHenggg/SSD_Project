<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$pageController = new StudentPageController($control);

$result = $pageController->loginStudent($_POST);

if ($result['success']) {
    // $_SESSION['user'] = $result['user'];  // Store student object or key details
    header('Location: dashboard.php');
    exit;
} else {
    $_SESSION['login_error'] = $result['message'];
    header('Location: login.php');
    exit;
}
?>