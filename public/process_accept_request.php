<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

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

// Get input data
$requestId = isset($_POST['requestId']) ? (int) $_POST['requestId'] : null;
$requesterId = isset($_POST['requesterId']) ? (int) $_POST['requesterId'] : null;
$approverId = SessionManager::getUser()['id'] ?? null;

if (!$requestId || !$requesterId || !$approverId) {
    SessionManager::setError("Invalid request data.");
    header("Location: group_requests.php?error=invalid");
    exit;
}

try {
    $groupMembershipController->onAcceptJoinRequest($requestId, $requesterId, $approverId);
    SessionManager::setSuccess("Join request accepted successfully.");
} catch (Exception $e) {
    SessionManager::setError("Error accepting join request: " . $e->getMessage());
}
header("Location: dashboard.php");
exit;
?>