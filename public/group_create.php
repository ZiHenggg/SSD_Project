<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use Ngmin\Ict2216G5\Concrete\GroupRepo;

// Initialize GroupRepository
$groupRepo = new GroupRepo($pdo);

$title = "Create Group";
ob_start();
?>

<div class="container">
    <div class="title text-center">
        <h2 class="m-0">Create Group </h2>
    </div>

    <div class="form-wrapper">
        <form action="process_create_group.php" method="POST">
            
            <label for="acadYear">Academic Year</label>
            <select name="acadYear" id="acadYear" class="form-select" required>
                <option value="">Select</option>
                <option value="AY2024/25">AY2024/25</option>
                <option value="AY2025/26">AY2025/26</option>
                <option value="AY2026/27">AY2026/27</option>
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
            <input type="text" name="moduleCode" id="moduleCode" class="form-control" placeholder="Enter Module Code" required>

            <label for="labGroup">Lab Group</label>
            <input type="text" name="labGroup" id="labGroup" class="form-control" placeholder="Enter Lab Group" required>

            <label for="noOfMembers">No of members</label>
            <input type="number" name="noOfMembers" id="noOfMembers" class="form-control" placeholder="Enter No of members" min="0" required>

            <label for="maxGroupSize">Max Group Size</label>
            <input type="number" name="maxGroupSize" id="maxGroupSize" class="form-control" placeholder="Enter Max Group Size" min="1" required>

            <label for="groupStatus">Group Status</label>
            <select name="groupStatus" id="groupStatus" class="form-select" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="archived">Archived</option>
            </select>

            <div class="form-buttons mt-4">
                <a href="groups.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Submit</button>
            </div>

        </form>
    </div>
</div>


<?php
$content = ob_get_clean();
include '_layout.php';
?>