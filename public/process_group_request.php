<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Redis

use App\Boundary\GroupMembershipController;
use App\Control\GroupMembershipControl;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupMapper;
use Predis\Client as RedisClient;

// Redis setup
$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379,
]);

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);

$groupId = $_POST['groupId'] ?? null;
$studentId = $_SESSION['user']['id'] ?? null;

if (!$groupId || !$studentId) {
    header("Location: group_info.php?groupId=$groupId&error=invalid");
    exit;
}

// Rate limiting
$rateKey = "join_request:student:$studentId";
$maxRequests = 5;
$windowSeconds = 600; // 10 minutes

if ((int)$redis->get($rateKey) >= $maxRequests) {
    $_SESSION['error'] = "You’ve reached the join request limit. Please try again later.";
    header("Location: group_info.php?groupId=$groupId");
    exit;
}

try {
    $groupMembershipController->onJoinGroupRequest($groupId, $studentId);
    $_SESSION['success'] = "Join request sent successfully.";

    // Increment Redis counter
    $redis->incr($rateKey);
    if ($redis->ttl($rateKey) <= 0) {
        $redis->expire($rateKey, $windowSeconds);
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Error sending join request: " . $e->getMessage();
}

header("Location: group_info.php?groupId=$groupId");
exit;
?>
