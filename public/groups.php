<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\ModuleMapper;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupPageController;
use App\Boundary\GroupMembershipController;

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$moduleRepo = new moduleMapper($pdo);
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$controller = new GroupPageController($groupControl, $groupMembershipControl, $pdo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);

$query = $_GET['query'] ?? '';

// Fetch all active groups
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
            <h2 class="m-0">Available Groups<?= $query ? ' for "' . htmlspecialchars($query) . '"' : '' ?></h2>
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
?>