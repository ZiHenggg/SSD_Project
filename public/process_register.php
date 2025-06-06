<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Ngmin\Ict2216G5\Concrete\StudentRepoImpl;
use Ngmin\Ict2216G5\Control\StudentControl;
use Ngmin\Ict2216G5\Boundary\StudentPageController;

$repo = new StudentRepoImpl($pdo);
$control = new StudentControl($repo);
$pageController = new StudentPageController($control);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $pageController->registerStudent($_POST);
    $_SESSION['register_result'] = $result;
}

header('Location: register.php');
exit;
