<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\SessionManager;

$groupId = $_POST['groupId'] ?? null;
$studentId = SessionManager::get('user')['id'] ?? null;

if (!$groupId || !$studentId) {
    header("Location: group_info.php?groupId=$groupId&error=invalid");
    exit;
}

$groupMembershipController = $pageControllers['groupMembershipController'];

try {
    $groupMembershipController->onRemoveJoinRequest($groupId, $studentId);
    SessionManager::setSuccess("Join request removed.");
} catch (Exception $e) {
    SessionManager::setError("Error removing request: " . $e->getMessage());
}

header("Location: group_info.php?groupId=$groupId");
exit;
