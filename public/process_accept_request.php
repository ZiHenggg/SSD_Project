<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Boundary\GroupMembershipController;
use App\Control\GroupMembershipControl;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupMapper;

// Initialize control class
$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);

// Get input data
$requestId = isset($_POST['requestId']) ? (int) $_POST['requestId'] : null;
$requesterId = isset($_POST['requesterId']) ? (int) $_POST['requesterId'] : null;
$approverId = $_SESSION['user']['id'] ?? null;

if (!$requestId || !$requesterId || !$approverId) {
    $_SESSION['error'] = "Invalid request data.";
    header("Location: group_requests.php?error=invalid");
    exit;
}

try {
    $groupMembershipController->onAcceptJoinRequest($requestId, $requesterId, $approverId);
    $_SESSION['success'] = "Join request accepted successfully.";
} catch (Exception $e) {
    $_SESSION['error'] = "Error accepting join request: " . $e->getMessage();
}
header("Location: dashboard.php");
exit;
?>