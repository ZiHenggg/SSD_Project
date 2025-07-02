<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Redis

use Predis\Client as RedisClient;
use App\SessionManager;

// Redis setup
$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379,
]);

$groupMembershipController = $pageControllers['groupMembershipController'];

$groupId = $_POST['groupId'] ?? null;
$studentId = SessionManager::get('user')['id'] ?? null;

if (!$groupId || !$studentId) {
    header("Location: group_info.php?groupId=$groupId&error=invalid");
    exit;
}

// Rate limiting
$rateKey = "join_request:student:$studentId";
$maxRequests = 5;
$windowSeconds = 600; // 10 minutes

if ((int)$redis->get($rateKey) >= $maxRequests) {
    SessionManager::setError("You've reached the join request limit. Please try again later.");
    header("Location: group_info.php?groupId=$groupId");
    exit;
}

try {
    $groupMembershipController->onJoinGroupRequest($groupId, $studentId);
    SessionManager::setSuccess("Join request sent successfully.");

    // Increment Redis counter
    $redis->incr($rateKey);
    if ($redis->ttl($rateKey) <= 0) {
        $redis->expire($rateKey, $windowSeconds);
    }

} catch (Exception $e) {
    SessionManager::setError("Error sending join request: " . $e->getMessage());
}

header("Location: group_info.php?groupId=$groupId");
exit;
?>
