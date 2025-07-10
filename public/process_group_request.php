<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php';
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

$groupMembershipController = $pageControllers['groupMembershipController'];
$actionController = $pageControllers['actionController'];
$actionType = 'join_request'; 

$groupId = $_POST['groupId'] ?? null;
$studentId = SessionManager::getUser()['id'] ?? null;

if (!$groupId || !$studentId) {
    header("Location: group_info.php?groupId=$groupId&error=invalid");
    exit;
}

try {
    // Rate limit check
    $actionController->onUserAction($studentId, $actionType);
    $groupMembershipController->onJoinGroupRequest($groupId, $studentId);
    SessionManager::setSuccess("Join request sent successfully.");

} catch (Exception $e) {
    SessionManager::setError("Error sending join request: " . $e->getMessage());
}

header("Location: group_info.php?groupId=$groupId");
exit;
?>
