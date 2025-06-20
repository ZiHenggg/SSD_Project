<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Boundary\GroupMembershipController;
use App\Control\GroupMembershipControl;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupMapper;
// use App\Entity\GroupJoinRequests;

// Initialize control class
$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);


$groupId = $_POST['groupId'] ?? null;
$studentId = $_SESSION['user']['id'] ?? null;

if (!$groupId || !$studentId) {
    header("Location: group_info.php?groupId=$groupId&error=invalid");
    exit;
}

// if (!$mapper->requestExists($groupId, $studentId)) {
//     $request = new GroupJoinRequests(
//         0,
//         (int) $groupId,
//         (int) $studentId,
//         'pending',
//         new DateTime()
//     );

//     $mapper->addRequest($request);
// }

try {
    $groupMembershipController->onJoinGroupRequest($groupId, $studentId);
    $_SESSION['success'] = "Join request sent successfully.";
} catch (Exception $e) {
    $_SESSION['error'] = "Error sending join request: " . $e->getMessage();
}

header("Location: group_info.php?groupId=$groupId");
exit;
?>