<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../src/CsrfManager.php';

use App\SessionManager;

// CSRF check before doing anything else
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfManager::validateToken($_POST['csrf_token'] ?? '')) {
        SessionManager::destroy();
        header('Location: error.php');
        exit;
    }
}

$groupId = $_POST['groupId'] ?? null;
$studentId = SessionManager::getUser()['id'] ?? null;

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
