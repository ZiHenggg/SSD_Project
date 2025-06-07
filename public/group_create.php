<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use Ngmin\Ict2216G5\Mapper\GroupMapper;

// Initialize GroupRepository
$groupRepo = new GroupMapper($pdo);

// Fetch modules from the database
$moduleStmt = $pdo->query("SELECT moduleCode FROM modules");
$modules = $moduleStmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch lab groups from the database
$labGroupStmt = $pdo->query("SELECT labGroupCode, moduleCode FROM labGroups");
$labGroupRows = $labGroupStmt->fetchAll(PDO::FETCH_ASSOC);

// Group lab groups by moduleCode
$labGroups = [];
foreach ($labGroupRows as $row) {
    $labGroups[$row['moduleCode']][] = $row['labGroupCode'];
}

$title = "Create Group";
ob_start();
?>

<div class="container">
    <div class="title text-center">
        <h2 class="m-0">Create Group </h2>
    </div>

    <div class="form-wrapper">
        <form action="process_group_create.php" method="POST">

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
            <label for="acadYear">Academic Year</label>
            <select name="acadYear" id="acadYear" class="form-select" required>
                <option value="">Select</option>
                <option value="2024/25">2024/25</option>
                <option value="2025/26">2025/26</option>
                <option value="2026/27">2026/27</option>
            </select>

            <label>Trimester</label>
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="trimester" id="tri1" value="1" required>
                    <label class="form-check-label" for="tri1">Trimester 1</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="trimester" id="tri2" value="2">
                    <label class="form-check-label" for="tri2">Trimester 2</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="trimester" id="tri3" value="3">
                    <label class="form-check-label" for="tri3">Trimester 3</label>
                </div>
            </div>

            <label for="moduleCode">Module Code</label>
            <select name="moduleCode" id="moduleCode" class="form-select" required>
                <option value="">Select Module</option>
                <?php foreach ($modules as $mod): ?>
                    <option value="<?= htmlspecialchars($mod) ?>"><?= htmlspecialchars($mod) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="labGroup">Lab Group</label>
            <select name="labGroup" id="labGroup" class="form-select" disabled required>
                <option value="">Select Lab Group</option>
            </select>

            <label for="maxGroupSize">Max Group Size</label>
            <input type="number" name="maxGroupSize" id="maxGroupSize" class="form-control"
                placeholder="Enter Max Group Size" min="1" required>

            <!-- <label for="groupStatus">Group Status</label>
            <select name="groupStatus" id="groupStatus" class="form-select" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="archived">Archived</option>
            </select> -->

            <div class="form-buttons mt-4">
                <a href="groups.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Submit</button>
            </div>

        </form>
    </div>
</div>

<script>
    const allLabGroups = <?= json_encode($labGroups) ?>;

    const moduleSelect = document.getElementById('moduleCode');
    const labGroupSelect = document.getElementById('labGroup');

    moduleSelect.addEventListener('change', () => {
        const selectedModule = moduleSelect.value;
        const groups = allLabGroups[selectedModule] || [];

        labGroupSelect.innerHTML = '';

        if (groups.length > 0) {
            labGroupSelect.disabled = false;
            labGroupSelect.required = true;

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select Lab Group';
            labGroupSelect.appendChild(defaultOption);

            groups.forEach(group => {
                const option = document.createElement('option');
                option.value = group;
                option.textContent = group;
                labGroupSelect.appendChild(option);
            });
        } else {
            labGroupSelect.disabled = true;
            labGroupSelect.required = false;
            const option = document.createElement('option');
            option.textContent = 'No lab groups available';
            labGroupSelect.appendChild(option);
        }
    });
</script>
<?php
$content = ob_get_clean();
include '_layout.php';
?>