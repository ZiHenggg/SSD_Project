<?php
require_once __DIR__ . '/../vendor/autoload.php';

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

use App\Service\AuthService; // ✅ Make sure this line is added
use App\SessionManager;

SessionManager::start();

function logEvent(string $type, string $message, array $context = []): void
{
    $logPath = '/var/www/logs/app.log';
    $ip = $_SERVER['HTTP_X_REAL_IP']
        ?? $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'unknown';

    $userData = SessionManager::getUser();
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

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env.prod');
$dotenv->load();

require_once __DIR__ . '/db.php';

// Instantiate mappers
$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$studentRepo = new StudentMapper($pdo);
$reviewRepo = new ReviewMapper($pdo);
$replyRepo = new ReplyMapper($pdo);
$moduleRepo = new ModuleMapper($pdo);
$labGroupRepo = new LabGroupMapper($pdo);
$studentStatsRepo = new StudentStatsMapper($pdo);

// Instantiate controls
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo, $labGroupRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$studentControl = new StudentControl($studentRepo);
$reviewControl = new ReviewControl($reviewRepo, $studentStatsRepo);
$replyControl = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);

// ✅ FIX: Create AuthService instance
$authService = new AuthService();

// Instantiate controllers
$groupPageController = new GroupPageController($groupControl, $groupMembershipControl, $pdo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl);
$studentPageController = new StudentPageController($studentControl, $authService); // ✅ Now passing 2 args
$reviewPageController = new ReviewPageController($reviewControl);
$replyPageController = new ReplyPageController($replyControl);

// Return config
return [
    'studentControl' => $studentControl,
    'groupPageController' => $groupPageController,
    'groupMembershipController' => $groupMembershipController,
    'studentPageController' => $studentPageController,
    'reviewPageController' => $reviewPageController,
    'replyPageController' => $replyPageController,
];
