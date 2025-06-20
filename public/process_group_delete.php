<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Control\GroupControl;
use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\ModuleMapper;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupPageController;
// Initialize control class
$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$moduleRepo = new moduleMapper($pdo);
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$groupPageController = new GroupPageController($groupControl, $groupMembershipControl, $pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_id'], $_POST['delete_group'])) {
    $groupId = (int) $_POST['group_id'];
    $groupPageController->onDeleteGroup($groupId);
    $_SESSION['success'] = "Group deleted successfully.";
    header("Location: dashboard.php");
    exit;
} else {
    $_SESSION['error'] = "Invalid group deletion request.";
    header("Location: dashboard.php");
    exit;
}

?>