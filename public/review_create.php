<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\StudentMapper;
use App\Mapper\GroupMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\ReviewMapper;
use App\Mapper\ModuleMapper;
use App\Control\GroupControl;
use App\Control\StudentControl;
use App\Control\GroupMembershipControl;
use App\Control\ReviewControl;
use App\Boundary\GroupPageController;
use App\Boundary\GroupMembershipController;
use App\Boundary\StudentPageController;
use App\Boundary\ReviewPageController;

$groupRepo = new GroupMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$studentRepo = new StudentMapper($pdo);
$reviewRepo = new ReviewMapper($pdo);
$moduleRepo = new moduleMapper($pdo);

$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$studentControl = new StudentControl($studentRepo);
$reviewControl = new ReviewControl($reviewRepo);

$groupController = new GroupPageController($groupControl, $groupMembershipControl, $pdo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);
$studentController = new StudentPageController($studentControl, $pdo);
$reviewController = new ReviewPageController($reviewControl, $pdo);

$reviewerId = $_SESSION['user']['id'];

if (isset($_GET['cancel'])) {
    unset($_SESSION['review_context']);
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $revieweeId = (int) ($_POST['reviewee_id'] ?? 0);
    $groupId = (int) ($_POST['group_id'] ?? 0);

    // Validate input
    if ($revieweeId <= 0 || $groupId <= 0 || $revieweeId === $reviewerId) {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: dashboard.php");
        exit;
    }

    // Check both users are in the group
    if (
        !$groupMembershipController->OnCheckIfMember($groupId, $reviewerId) ||
        !$groupMembershipController->OnCheckIfMember($groupId, $revieweeId)
    ) {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: dashboard.php");
        exit;
    }

    // Check if user has already reviewed
    if ($reviewController->onCheckIfReviewed($reviewerId, $revieweeId, $groupId)) {
        $_SESSION['error'] = "You have already reviewed this user.";
        header("Location: dashboard.php");
        exit;
    }

    // Store context in session
    $_SESSION['review_context'] = [
        'reviewer_id' => $reviewerId,
        'reviewee_id' => $revieweeId,
        'group_id' => $groupId
    ];
}

// Display review form
$context = $_SESSION['review_context'] ?? null;

if (!$context) {
    $_SESSION['error'] = "Something went wrong. Please try again.";
    header("Location: dashboard.php");
    exit;
}

$revieweeId = $context['reviewee_id'];
$groupId = $context['group_id'];

// Fetch display data
$groupInfo = $groupController->displayGroupDetails($groupId);
$reviewee = $studentController->showUserProfile($revieweeId);

if (!$groupInfo || !$reviewee) {
    unset($_SESSION['review_context']);
    $_SESSION['error'] = "Something went wrong. Please try again.";
    header("Location: dashboard.php");
    exit;
}

$groupDetails = $groupInfo['group'];
$revieweeName = $reviewee->getStudentName();

$title = "Create Review";
ob_start();
?>

<!-- Page-specific content starts here -->
<div class="container">
    <div class="form-wrapper">
        <?php displayErrorMessage(); ?>
        <?php displaySuccessMessage(); ?>
        <div class="title">
            <h2><?= htmlspecialchars($groupDetails->getGroupName()) ?></h2>
            <h4><?= htmlspecialchars($groupDetails->getModuleCode()) ?></h4>
            <h5 class="my-4">Reviewing <?= htmlspecialchars($revieweeName) ?>, <?= htmlspecialchars($revieweeId) ?></h5>
        </div>

        <form class="my-4" action="process_review_create.php" method="POST">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <label for="rating">Rating</label>
            <select name="rating" id="rating" class="form-select" required>
                <option value="">Choose Rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>

            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" placeholder="Enter Description" required></textarea>

            <div class="form-buttons mt-4">
                <a href="review_create.php?cancel=1" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Submit</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '_layout.php';
?>