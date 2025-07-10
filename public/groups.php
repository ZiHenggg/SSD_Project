<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\SessionManager;

$studentId = SessionManager::get('user')['id'] ?? $_SERVER['REMOTE_ADDR'];
$actionController = $pageControllers['actionController'];

if (!$actionController->onUserAction($studentId, 'search')) {
    $title = "Groups";
ob_start();
?>
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

$groupController = $pageControllers['groupPageController'];
$groupMembershipController = $pageControllers['groupMembershipController'];

$query = $_GET['query'] ?? '';

$groups = $query
    ? $groupController->onSearchGroupsByModuleName($query)
    : $groupController->listAllActiveGroups();

$noGroups = count($groups);
$studentId = SessionManager::get('user')['id'] ?? null;

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
            $module = $groupController->onGetModuleByGroupId($group->getGroupId());
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
