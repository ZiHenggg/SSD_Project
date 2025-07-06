<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/CsrfManager.php';

// Set default timezone to Singapore
date_default_timezone_set('Asia/Singapore');

use App\Mapper\GroupMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\StudentMapper;
use App\Mapper\ReviewMapper;
use App\Mapper\ReplyMapper;
use App\Mapper\ModuleMapper;
use App\Mapper\LabGroupMapper;
use App\Mapper\StudentStatsMapper;

use App\Control\GroupControl;
use App\Control\GroupJoinRequestsControl;
use App\Control\GroupMembershipControl;
use App\Control\StudentControl;
use App\Control\ReviewControl;
use App\Control\ReplyControl;

use App\Boundary\GroupPageController;
use App\Boundary\GroupMembershipController;
use App\Boundary\StudentPageController;
use App\Boundary\ReviewPageController;
use App\Boundary\ReplyPageController;

use App\SessionManager;

// 🔴 Commented out error handlers for debugging
/*
set_exception_handler(function ($e) {
    error_log("Uncaught exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    if (!headers_sent()) {
        header("Location: /error.php");
        exit();
    }
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    if (!headers_sent()) {
        header("Location: /error.php");
        exit();
    }
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log("Fatal error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
        if (!headers_sent()) {
            header("Location: /error.php");
            exit();
        }
    }
});
*/

// Start session only if not already active
SessionManager::start();

function logEvent(string $type, string $message, array $context = []): void
{
    $logPath = '/var/www/logs/app.log';

    // Real client IP first, fallback to Docker internal
    $ip = $_SERVER['HTTP_X_REAL_IP']
        ?? $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'unknown';

    $userData = \App\SessionManager::getUser();
    $user = $userData['id'] ?? 'guest';

    $entry = sprintf(
        "[%s] [%s] %s | User: %s | IP: %s | %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($type),
        $message,
        $user,
        $ip,
        json_encode($context)
    );

    file_put_contents($logPath, $entry, FILE_APPEND);
}


function displayErrorMessage(): void
{
    if (!empty(SessionManager::getError())) {
        echo '<div class="alert alert-danger">' . htmlspecialchars(SessionManager::getError()) . '</div>';
        SessionManager::setError(null);
    }
}

function displaySuccessMessage(): void
{
    if (!empty(SessionManager::getSuccess())) {
        echo '<div class="alert alert-success">' . htmlspecialchars(SessionManager::getSuccess()) . '</div>';
        SessionManager::setSuccess(null);
    }
}

// Autoload classes from Composer (e.g. Dotenv, custom namespaces)
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env.prod');
$dotenv->load();

require_once __DIR__ . '/db.php';

// Instantiate mappers and controls
$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$studentRepo = new StudentMapper($pdo);
$reviewRepo = new ReviewMapper($pdo);
$replyRepo = new ReplyMapper($pdo);
$moduleRepo = new ModuleMapper($pdo);
$labGroupRepo = new LabGroupMapper($pdo);
$studentStatsRepo = new StudentStatsMapper($pdo);

$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo, $labGroupRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$studentControl = new StudentControl($studentRepo);
$reviewControl = new ReviewControl($reviewRepo, $studentStatsRepo);
$replyControl = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);

$groupPageController = new GroupPageController($groupControl, $groupMembershipControl);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $groupControl);
$studentPageController = new StudentPageController($studentControl, $reviewControl);
$reviewPageController = new ReviewPageController($reviewControl);
$replyPageController = new ReplyPageController($replyControl, $reviewControl);

return [
    'studentControl' => $studentControl,
    'groupPageController' => $groupPageController,
    'groupMembershipController' => $groupMembershipController,
    'studentPageController' => $studentPageController,
    'reviewPageController' => $reviewPageController,
    'replyPageController' => $replyPageController,
];
