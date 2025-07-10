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

$studentId = SessionManager::getUser()['id'] ?? 0;

$groupPageController = $pageControllers['groupPageController'];
$actionController = $pageControllers['actionController'];
$actionType = 'create_group';

try {
    // Rate limit check
    $actionController->onUserAction($studentId, $actionType);
    $groupPageController->onCreateGroup($_POST, $studentId);

    header("Location: dashboard.php");
    exit;

} catch (Exception $e) {
    logEvent('error', 'Group creation failed', [
        'studentId' => $studentId,
        'error' => $e->getMessage()
    ]);

    SessionManager::setError($e->getMessage());
    header("Location: group_create.php");
    exit;
}
