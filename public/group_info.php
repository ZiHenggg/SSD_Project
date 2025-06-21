<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\ModuleMapper;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use App\Control\GroupJoinRequestsControl;
use App\Boundary\GroupPageController;
use App\Boundary\GroupMembershipController;
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

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$moduleRepo = new moduleMapper($pdo);
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$groupController = new GroupPageController($groupControl, $groupMembershipControl, $pdo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);

$hasRequested = $groupMembershipController->onCheckIfRequested($groupId, $studentId);
$isMember = $groupMembershipController->onCheckIfMember($groupId, $studentId);
$groupData = $groupController->displayGroupDetails($groupId);
$group = $groupData['group'];
$members = $groupData['members'];
$moduleData = $groupData['module'];

?>

<div class="group-info container">
    <div class="group-header">
        <div class="group-title">
            <h2><?= htmlspecialchars($group->getGroupName()) ?></h2>
            <p><?= htmlspecialchars($group->getModuleCode()) ?>, <?= htmlspecialchars($moduleData->getModuleName($group->getModuleCode())) ?></p>
        </div>
        <?php if (!$isMember && count($members) < $group->getMaxMembers()): ?>
            <form method="POST" action="<?= $hasRequested ? 'process_remove_request.php' : 'process_group_request.php' ?>" style="display: inline;">
                <input type="hidden" name="groupId" value="<?= $groupId ?>">
                <?php if ($hasRequested): ?>
                    <button class="join-button" type="submit">Requested</button>
                <?php else: ?>
                    <button class="join-button" type="submit">Request to Join</button>
                <?php endif; ?>
            </form>
        <?php elseif (!$isMember): ?>
            <button class="join-button" disabled>Group Full</button>
        <?php endif; ?>
    </div>

    <div class="members-section">
        <h3>Members (<?= count($members) ?>/<?= $group->getMaxMembers() ?>)</h3>
        <ol class="info-item showing current-members name-list">
            <?php foreach ($members as $member):
                if (($member->getRole() === 'admin') && ($member->getStudentId() == $studentId)): ?>
                    <li class="user admin">
                <?php elseif($member->getRole() === 'admin'): ?>
                    <li class="admin">
                <?php elseif($member->getStudentId() == $studentId): ?>
                    <li class="user">
                <?php else: ?>
                    <li>
                <?php endif; ?>
                        <div class="member-wrapper">
                            <a class="profile-link text-decoration-none" href="profile.php?id=<?=$member->getStudentId()?>"><?= htmlspecialchars($member->getStudentName())?>, <?= htmlspecialchars($member->getStudentId())?></a>
                        </div>
                    </li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
?>