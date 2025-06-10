<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupPageController;

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$controller = new GroupPageController($groupControl, $groupMembershipControl, $pdo);

// Fetch all active groups
$groups = $controller->listAllActiveGroups();

$title = "Groups";
ob_start();
?>

<div class="container">
    <div class="group-wrapper">
        <div class="title d-flex justify-content-between mb-4">
            <h2 class="m-0">Available Groups for </h2>
            <a href="group_create.php" class="text-decoration-none d-flex">
                <img src="img/plus.svg" alt="Add Group" class="w-100 add-group" />
            </a>
        </div>

        <?php if (count($groups) > 0): ?>
            <?php foreach ($groups as $group): ?>
                <div class="group-item">
                    <a href="group_info.php?groupId=<?= $group->getGroupId() ?>">
                        <?= htmlspecialchars($group->getGroupName()) ?>
                    </a>

                    <!-- <span class="group-name header"><?= htmlspecialchars($group->getGroupName()) ?></span> -->
                    <span class="module"><?= htmlspecialchars($group->getModuleCode()) ?></span>
                    <span class="acad-term">[<?= htmlspecialchars($group->getAcadYear()) ?>]</span>
                    <span class="member-count"><?= $group->getNoOfMembers() ?>/<?= $group->getMaxMembers() ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No groups found.</p>
        <?php endif; ?>

    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
?>