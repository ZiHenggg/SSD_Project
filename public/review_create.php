<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\GroupMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\GroupMembershipMapper;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupPageController;
use App\Boundary\GroupMembershipController;

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);

$groupControl = new GroupControl($groupRepo, $groupMembershipRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);

$groupController = new GroupPageController($groupControl, $groupMembershipControl, $pdo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);

$reviewerId = $_SESSION['user']['id'];

if (isset($_GET['cancel'])) {
    unset($_SESSION['review_context']);
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $revieweeId = (int) ($_POST['reviewee_id'] ?? 0);
    $groupId = (int) ($_POST['group_id'] ?? 0);

    if ($revieweeId <= 0 || $groupId <= 0) {
        header("Location: dashboard.php");
        exit;
    } else if ($revieweeId === $reviewerId) {
        header("Location: dashboard.php");
        exit;
    } else if (!$groupMembershipController->OnCheckIfMember($groupId, $reviewerId) || !$groupMembershipController->OnCheckIfMember($groupId, $revieweeId)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['review_context'] = [
            'reviewer_id' => $reviewerId,
            'reviewee_id' => $revieweeId,
            'group_id' => $groupId
        ];
    }

    // refresh the page to avoid resubmission and clean up POST data
    header("Location: review_create.php");
    exit;
    

} else {
    $context = $_SESSION['review_context'] ?? null;
    if (!$context) {
        header("Location: dashboard.php");
        exit;
    }
    
    $revieweeId = $context['reviewee_id'];
    $groupId = $context['group_id'];

    $groupInfo = $groupController->displayGroupDetails($groupId);
    if (!$groupInfo) {
        unset($_SESSION['review_context']);
        header("Location: dashboard.php");
        exit;
    } else {
        $groupInfo = $groupInfo['group'];
    }
}


$title = "Create Review";
ob_start();
?>

<!-- Page-specific content starts here -->
<div class="container">
    <div class="form-wrapper">
        <div class="title">
            <h2><?= htmlspecialchars($groupInfo->getGroupName()) ?></h2>
            <h4><?= htmlspecialchars($groupInfo->getModuleCode()) ?></h4>
        </div>

        <form class="my-4" action="process_review_create.php" method="POST">

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

            <label for="rating">Rating</label>
            <select name="rating" id="rating" class="form-select" required>
                <option value="">Choose Rating</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>

            <label for="description">Description</label>
            <input type="textfield" name="description" id="description" class="form-control"
                placeholder="Enter Description" required>

            <div class="form-buttons mt-4">
                <a href="review_create.php?cancel=1" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Submit</button>
            </div>

        </form>
    </div>
</div>
<!-- Page-specific content ends -->

<?php
$content = ob_get_clean();
include '_layout.php';
?>