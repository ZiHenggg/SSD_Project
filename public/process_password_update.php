<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$pageController = new StudentPageController($control);

$result = $pageController->updatePassword($_POST);

if ($result['success']) {
    $_SESSION['change_pw_success'] = $result['message'];
    header('Location: dashboard.php'); // Or settings page
    exit;
} else {
    $_SESSION['change_pw_error'] = $result['message'];
    header('Location: change_password.php');
    exit;
}
?>