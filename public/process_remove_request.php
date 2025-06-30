<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Boundary\GroupMembershipController;
use App\Control\GroupMembershipControl;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupMapper;
use App\SessionManager;

$groupId = $_POST['groupId'] ?? null;
$studentId = SessionManager::get('user')['id'] ?? null;

if (!$groupId || !$studentId) {
    header("Location: group_info.php?groupId=$groupId&error=invalid");
    exit;
}

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);

try {
    $groupMembershipController->onRemoveJoinRequest($groupId, $studentId);
    SessionManager::setSuccess("Join request removed.");
} catch (Exception $e) {
    SessionManager::setError("Error removing request: " . $e->getMessage());
}

header("Location: group_info.php?groupId=$groupId");
exit;
