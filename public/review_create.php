<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\SessionManager;

$groupController = $pageControllers['groupPageController'];
$groupMembershipController = $pageControllers['groupMembershipController'];
$studentController = $pageControllers['studentPageController'];
$reviewController = $pageControllers['reviewPageController'];

$reviewerId =SessionManager::getUser()['id'];

if (isset($_GET['cancel'])) {
    unset($_SESSION['review_context']);
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $revieweeId = (int) ($_POST['reviewee_id'] ?? 0);
    $groupId = (int) ($_POST['group_id'] ?? 0);

    $group = $groupController->displayGroupDetails($groupId)['group'] ?? null;

    // Validate input
    if ($revieweeId <= 0 || $groupId <= 0 || $revieweeId === $reviewerId) {
        SessionManager::setError("Something went wrong. Please try again.");
        header("Location: dashboard.php");
        exit;
    }

    // Check both users are in the group
    if (
        !$groupMembershipController->OnCheckIfMember($groupId, $reviewerId) ||
        !$groupMembershipController->OnCheckIfMember($groupId, $revieweeId)
    ) {
        SessionManager::setError("Something went wrong. Please try again.");
        header("Location: dashboard.php");
        exit;
    }

    if ($group->getGroupStatus() !== 'active') {
        SessionManager::setError("Group has been archived. Cannot submit review.");
        header("Location: dashboard.php");
        exit;
    }

    // Check if user has already reviewed
    if ($reviewController->onCheckIfReviewed($reviewerId, $revieweeId, $groupId)) {
        SessionManager::setError("You have already reviewed this user.");
        header("Location: dashboard.php");
        exit;
    }

    // Store context in session
    SessionManager::setReviewContext([
        'reviewer_id' => $reviewerId,
        'reviewee_id' => $revieweeId,
        'group_id'    => $groupId
    ]);
}

// Display review form
$context = SessionManager::getReviewContext();

if (!$context) {
    SessionManager::setError("Something went wrong. Please try again.");
    header("Location: dashboard.php");
    exit;
}

$revieweeId = $context['reviewee_id'];
$groupId = $context['group_id'];

// Fetch display data
$groupInfo = $groupController->displayGroupDetails($groupId);
$reviewee = $studentController->showUserProfile($revieweeId);

if (!$groupInfo || !$reviewee) {
    SessionManager::clearReviewContext();
    SessionManager::setError("Something went wrong. Please try again.");
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
            <?php if (SessionManager::getError()): ?>
                <div class="alert alert-danger"><?= htmlspecialchars(SessionManager::getError()) ?></div>
                <?php SessionManager::setError(null); ?>
            <?php endif; ?>
            
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CsrfManager::generateToken()) ?>">

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