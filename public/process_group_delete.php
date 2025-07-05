<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../src/CsrfManager.php';

use App\SessionManager;

$groupPageController = $pageControllers['groupPageController'];
$groupMembershipController = $pageControllers['groupMembershipController'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_id'], $_POST['delete_group'])) {
    
    // CSRF check before doing anything else
    if (!CsrfManager::validateToken($_POST['csrf_token'] ?? '')) {
        SessionManager::destroy();
        header('Location: error.php');
        exit;
    }

    $groupId = (int) $_POST['group_id'];
    $studentId = SessionManager::getUser()['id'] ?? null;
    $ifMember = $groupMembershipController->onCheckIfMember($groupId, $studentId);
    $isAdmin = $groupMembershipController->onCheckUserRole($groupId, $studentId) === 'admin';

    if (!$ifMember) {
        SessionManager::setError("You are not a member of this group.");
    } else if (!$isAdmin) {
        SessionManager::setError("You do not have permission to delete this group.");
    } else {
        $groupMembershipController->removeRemainingRequests($groupId, $studentId);
        $groupPageController->onDeleteGroup($groupId);
        SessionManager::setSuccess("Group deleted successfully.");
    }

    header("Location: dashboard.php");
    exit;
}
