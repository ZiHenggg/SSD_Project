<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;

$title = "Group Info";
ob_start();

$groupId = isset($_GET['groupId']) ? (int)$_GET['groupId'] : null;

if (!$groupId) {
    echo "<p>Invalid group ID.</p>";
    $content = ob_get_clean();
    include '_layout.php';
    exit;
}

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
        <button class="join-button">Request to Join</button>
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