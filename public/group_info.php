<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupJoinRequestsMapper;

$title = "Group Info";
ob_start();

$studentId = $_SESSION['user']['id'] ?? null;
$groupId = isset($_GET['groupId']) ? (int)$_GET['groupId'] : null;

if (!$groupId) {
    echo "<p>Invalid group ID.</p>";
    $content = ob_get_clean();
    include '_layout.php';
    exit;
}

$requestMapper = new GroupJoinRequestsMapper($pdo);
$hasRequested = $requestMapper->requestExists($studentId, $groupId);

$groupMapper = new GroupMapper($pdo);
$memberMapper = new GroupMembershipMapper($pdo);
                  
$group = $groupMapper->getGroup($groupId);
$members = $memberMapper->getMembers($groupId);

?>

<div class="container">
    <div class="group-header">
        <div class="group-title">
            <h2><?= htmlspecialchars($group->getGroupName()) ?></h2>
            <p><?= htmlspecialchars($group->getModuleCode()) ?> [<?= htmlspecialchars($group->getAcadYear()) ?> <?= htmlspecialchars($group->getTrimester()) ?>]</p>
        </div>
        <form method="POST" action="process_group_request.php" style="display: inline;">
            <input type="hidden" name="groupId" value="<?= $groupId ?>">
            <?php if ($hasRequested): ?>
                <button class="join-button" disabled style="background-color: grey;">Requested</button>
            <?php else: ?>
                <button class="join-button" type="submit">Request to Join</button>
            <?php endif; ?>
        </form>

    </div>

    <div class="members-section">
        <h3>Members (<?= count($members) ?>/<?= $group->getMaxMembers() ?>)</h3>
        <ul class="members-list"> 
            <?php foreach ($members as $index => $member): ?>
                <li><span><?= ($index + 1) ?>. <?= htmlspecialchars($member->getStudentName()) ?>, <?= htmlspecialchars($member->getStudentId()) ?></span></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
?>