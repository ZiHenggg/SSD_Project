<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Control\GroupControl;
use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Boundary\GroupPageController;
use App\Control\GroupMembershipControl;

// Initialize control class
$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo);
$groupPageController = new GroupPageController($groupControl, $groupMembershipControl, $pdo);

try {
    $groupPageController->createGroup($_POST, $_SESSION['user']['id']);
    // Redirect or show success
    header("Location: groups.php");
    exit;
} catch (Exception $e) {
    // Handle error: log it or show message
    $_SESSION['error'] = $e->getMessage();
    header("Location: group_create.php");
    exit;
}



// TODO: Do we need to store previous inputs? in case of error?
?>