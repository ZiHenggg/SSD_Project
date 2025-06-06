<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use Ngmin\Ict2216G5\Concrete\GroupRepo;

// Initialize GroupRepository
$groupRepo = new GroupRepo($pdo);

// Fetch all active groups
$groups = $groupRepo->getAllActiveGroups();

$title = "Groups";
ob_start();
?>

<div class="container">
    <div class="group-wrapper">
        <div class="title d-flex justify-content-between mb-4">
            <h2 class="m-0">Available Groups for </h2>
            <a href="group_create.php" class="text-decoration-none d-flex">
                <img src="img/plus.svg" alt="Add Group" class="w-100 add-group"/>
            </a>
        </div>

        <?php if (count($groups) > 0): ?>
            <?php foreach ($groups as $group): ?>
                <div class="group-item">
                    <span class="group-name header"><?= htmlspecialchars($group->getGroupName()) ?></span>
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