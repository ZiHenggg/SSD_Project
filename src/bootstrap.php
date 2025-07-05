<?php
require_once __DIR__ . '/../vendor/autoload.php';

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

// Start session only if not already active
SessionManager::start();


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
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
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
?>