<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\SessionManager;

$groupPageController = $pageControllers['groupPageController'];
$groupMembershipController = $pageControllers['groupMembershipController'];

$studentId = SessionManager::getUser()['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_id'], $_POST['delete_group'])) {
    $groupId = (int) $_POST['group_id'];

    $ifMember = $groupMembershipController->onCheckIfMember($groupId, $studentId);
    $isAdmin = $groupMembershipController->onCheckUserRole($groupId, $studentId) === 'admin';

    if (!$ifMember) {
        logEvent('warn', 'Group deletion blocked – not a member', [
            'studentId' => $studentId,
            'groupId' => $groupId
        ]);
        SessionManager::setError("You are not a member of this group.");
    } elseif (!$isAdmin) {
        logEvent('warn', 'Group deletion blocked – not an admin', [
            'studentId' => $studentId,
            'groupId' => $groupId
        ]);
        SessionManager::setError("You do not have permission to delete this group.");
    } else {
        $groupPageController->onDeleteGroup($groupId);
        logEvent('info', 'Group deleted', [
            'studentId' => $studentId,
            'groupId' => $groupId
        ]);
        SessionManager::setSuccess("Group deleted successfully.");
    }

    header("Location: dashboard.php");
    exit;
} else {
    logEvent('warn', 'Invalid group deletion request');
    SessionManager::setError("Invalid group deletion request.");
    header("Location: dashboard.php");
    exit;
}
