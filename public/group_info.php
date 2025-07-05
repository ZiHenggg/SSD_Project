<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\SessionManager;

$title = "Group Info";
ob_start();

$studentId = SessionManager::get('user')['id'] ?? null;
$groupId = isset($_GET['groupId']) ? (int)$_GET['groupId'] : null;

if (!$groupId) {
    echo "<p>Invalid group ID.</p>";
    $content = ob_get_clean();
    include '_layout.php';
    exit;
}

$groupController = $pageControllers['groupPageController'];
$groupMembershipController = $pageControllers['groupMembershipController'];

$hasRequested = $groupMembershipController->onCheckIfRequested($groupId, $studentId);
$joinStatus = $groupMembershipController->displayRequestStatus($groupId, $studentId);
$isMember = $groupMembershipController->onCheckIfMember($groupId, $studentId);
$groupData = $groupController->displayGroupDetails($groupId);

if (!$groupData) {
    echo "<p>Group not found.</p>";
    $content = ob_get_clean();
    header("Location: groups.php");
}

$group = $groupData['group'];
$members = $groupData['members'];
$moduleData = $groupData['module'];

?>

<div class="group-info container">
    <?php displayErrorMessage(); ?>
    <?php displaySuccessMessage(); ?>
    <div class="group-header">
        <div class="group-title">
            <h2><?= htmlspecialchars($group->getGroupName()) ?></h2>
            <p><?= htmlspecialchars($group->getModuleCode()) ?>, <?= htmlspecialchars($moduleData->getModuleName($group->getModuleCode())) ?></p>
        </div>
        <?php if ($group->getGroupStatus() === 'inactive'): ?>
            <button class="join-button" disabled>Group Archived</button>
        <?php elseif (!$isMember && count($members) < $group->getMaxMembers()): ?>
            <form method="POST" action="<?= ($hasRequested && ($joinStatus === 'pending')) ? 'process_remove_request.php' : 'process_group_request.php' ?>" style="display: inline;">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CsrfManager::generateToken()) ?>">
                
                <input type="hidden" name="groupId" value="<?= $groupId ?>">
                <?php if ($hasRequested && ($joinStatus === 'pending')): ?>
                    <button class="join-button" type="submit">Cancel Request</button>
                <?php elseif ((!$hasRequested) || ($joinStatus === 'rejected')): ?>
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