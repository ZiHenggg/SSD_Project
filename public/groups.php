<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Redis

use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\ModuleMapper;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupPageController;
use App\Boundary\GroupMembershipController;
use Predis\Client as RedisClient;

// Redis rate limit
$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => 'redis',
    'port' => 6379,
]);

$studentId = $_SESSION['user']['id'] ?? $_SERVER['REMOTE_ADDR'];
$rateKey = "search:rate:$studentId";
$maxSearches = 10;
$timeWindow = 60; // 60 seconds

// Count and enforce
if ((int)$redis->get($rateKey) >= $maxSearches) {
    $title = "Groups";
    ob_start(); ?>
    <div class="container">
        <div class="alert alert-warning mt-5" role="alert">
            You’ve reached the search limit. Please wait a minute and try again.
        </div>
    </div>
<?php
    $content = ob_get_clean();
    include '_layout.php';
    exit;
}
$redis->incr($rateKey);
if ($redis->ttl($rateKey) <= 0) {
    $redis->expire($rateKey, $timeWindow);
}

// --- Proceed with normal logic ---
$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$moduleRepo = new ModuleMapper($pdo);
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$controller = new GroupPageController($groupControl, $groupMembershipControl, $pdo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);

$query = $_GET['query'] ?? '';
$groups = $query
    ? $controller->onSearchGroupsByModuleName($query)
    : $controller->listAllActiveGroups();

$noGroups = count($groups);
$studentId = $_SESSION['user']['id'] ?? null;

$title = "Groups";
ob_start();
?>

<div class="container">
    <div class="group-wrapper">
        <div class="title d-flex justify-content-between mb-4">
            <h2 class="m-0">Groups<?= $query ? ' for "' . htmlspecialchars($query) . '"' : '' ?></h2>
            <a href="group_create.php" class="text-decoration-none d-flex">
                <img src="img/plus.svg" alt="Add Group" class="w-100 add-group" />
            </a>
        </div>

        <?php 
        $anyShown = false;
        foreach ($groups as $group): 
            $module = $groupControl->getModuleByGroupId($group->getGroupId());
            $isMember = $groupMembershipController->onCheckIfMember($group->getGroupId(), $studentId);

            if ($isMember) continue;
            $anyShown = true;
        ?>
            <div class="group-item">
                <a class="group-name text-decoration-none header" href="group_info.php?groupId=<?= $group->getGroupId() ?>">
                    <?= htmlspecialchars($group->getGroupName()) ?>
                </a>
                <span class="module"><?= htmlspecialchars($group->getModuleCode()) ?>, <?= htmlspecialchars($module->getModuleName()) ?></span>
                <span class="acad-term">[<?= htmlspecialchars($group->getAcadYear()) ?>]</span>
                <span class="member-count"><?= $group->getNoOfMembers() ?>/<?= $group->getMaxMembers() ?></span>
            </div>
        <?php endforeach; ?>

        <?php if (!$anyShown): ?>
            <p>No groups found.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
