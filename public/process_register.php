<?php
require_once __DIR__ . '/../src/bootstrap.php';

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;

$repo = new StudentMapper($pdo);
$control = new StudentControl($repo);
$pageController = new StudentPageController($control);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $pageController->registerStudent($_POST);
    $_SESSION['register_result'] = $result;
}

header('Location: register.php');
exit;
