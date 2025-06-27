<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\StudentStatsMapper;
use App\Mapper\GroupJoinRequestsMapper;

use App\Mapper\GroupMembershipMapper;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupMembershipController;

use App\Mapper\GroupMapper;
use App\Control\GroupControl;
use App\Boundary\GroupPageController;

use App\Mapper\StudentMapper;
use App\Control\StudentControl;
use App\Boundary\StudentPageController;

use App\Mapper\ReplyMapper;
use App\Control\ReplyControl;
use App\Boundary\ReplyPageController;

use App\Mapper\ReviewMapper;
use App\Control\ReviewControl;
use App\Boundary\ReviewPageController;

use App\Mapper\ModuleMapper;

$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupRepo = new GroupMapper($pdo);
$studentRepo = new StudentMapper($pdo);
$reviewRepo = new ReviewMapper($pdo);
$replyRepo = new ReplyMapper($pdo);
$moduleRepo = new moduleMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$studentStatsRepo = new StudentStatsMapper($pdo);

$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
$studentControl = new StudentControl($studentRepo);
$reviewControl = new ReviewControl($reviewRepo, $studentStatsRepo);
$replyControl = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);

$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);
$groupController = new GroupPageController($groupControl, $groupMembershipControl, $pdo);
$studentController = new StudentPageController($studentControl, $pdo);
$reviewController = new ReviewPageController($reviewControl, $pdo);
$replyController = new ReplyPageController($replyControl, $pdo);

$loggedInId = $_SESSION['user']['id'];

try {
    if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
        $profileId = (int) $_GET['id'];
    } else if (isset($_GET['id']) && !ctype_digit($_GET['id'])) {
        header("Location: /profile.php?id=" . $loggedInId);
    } else {
        $profileId = $loggedInId;
    }
    $student = $studentController->showUserProfile($profileId);
    if (!$student) {
        header("Location: /profile.php?id=" . $loggedInId);
        exit;
    }
} catch (Exception $e) {
    header("Location: /profile.php?id=" . $loggedInId);
}

$receivedReviewResponse = $reviewController->onViewReceivedReviews($student->getStudentId());
$receivedReviews = $receivedReviewResponse['reviews'] ?? [];

if ($profileId === $loggedInId) {
    $myReviewResponse = $reviewController->onViewMyReviews($student->getStudentId());
    $myReviews = $myReviewResponse['reviews'] ?? [];
}

$groupJoinRequestsResponse = $groupMembershipController->displayStudentJoinRequests($student->getStudentId());
$groupJoinRequests = $groupJoinRequestsResponse['joinRequest'] ?? [];
$pendingJoinRequests = [];
foreach ($groupJoinRequests as $request) {
    if ($request->getJoinStatus() === 'pending') {
        $pendingJoinRequests[] = $request;
    }
}

$rejectedJoinRequests = [];
foreach ($groupJoinRequests as $request) {
    if ($request->getJoinStatus() === 'rejected') {
        $rejectedJoinRequests[] = $request;
    }
}

$acceptedJoinRequests = [];
foreach ($groupJoinRequests as $request) {
    if ($request->getJoinStatus() === 'accepted') {
        $acceptedJoinRequests[] = $request;
    }
}

$studentStatsResponse = $reviewController->displayReviewStats($student->getStudentId());
$totalReviews = $studentStatsResponse['totalReviews'] ?? 0;
$averageRating = $studentStatsResponse['averageRating'] ?? 0;

$maxStars = 5;

$title = "Profile";
ob_start();
?>

<!-- Page-specific content starts here -->
<div class="profile">
    <?php displayErrorMessage(); ?>
    <?php displaySuccessMessage(); ?>
    <div class="content-container d-flex justify-content-between">
        <div class="info-pending-wrapper">
            <h2 class="mb-5">
                <?php if ($profileId !== $loggedInId): ?>
                    Viewing <?= htmlspecialchars($student->getStudentName()) ?>'s Profile
                <?php else: ?>
                    My Profile
                <?php endif; ?>
            </h2>
            <div class="profile-info">
                <h3>Info</h3>
                <p><strong>Full Name:</strong> <?= htmlspecialchars($student->getStudentName()) ?></p>
                <p><strong>Student ID:</strong> <?= htmlspecialchars($student->getStudentId()) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($student->getEmail()) ?></p>

                <p><a href="change_password.php" class="btn btn-outline-primary btn-sm mt-2">Change Password</a></p>
            </div>
            <?php if ($profileId === $loggedInId): ?>
                <div class="mt-5 pending-requests">
                    <h3>Group Request Status</h3>
                    <h4>Pending</h4>
                    <?php if (count($pendingJoinRequests) > 0): ?>
                        <?php foreach ($pendingJoinRequests as $request): ?>
                            <?php
                            $groupData = $groupController->displayGroupDetails($request->getGroupId());
                            $group = $groupData['group'];
                            $moduleData = $groupData['module'];
                            ?>
                            <div class="mt-3 group-item">
                                <a href="group_info.php?groupId=<?= htmlspecialchars($group->getGroupId()) ?>"
                                    class="group-name text-decoration-none header"><?= htmlspecialchars($group->getGroupName()) ?></a>
                                <span class="module"><?= htmlspecialchars($group->getModuleCode()) ?>,
                                    <?= htmlspecialchars($moduleData->getModuleName($group->getModuleCode())) ?></span>
                                <span class="timestamp"><strong>Requested At:
                                    </strong><?= htmlspecialchars($request->getRequestedAt()->format('D, d M Y H:i:s')) ?></span>
                                <span
                                    class="member-count"><?= htmlspecialchars($group->getNoOfMembers()) ?>/<?= htmlspecialchars($group->getMaxMembers()) ?></span>

                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No requests are currently pending.</p>
                    <?php endif; ?>
                </div>
                <div class="mt-2 accepted-requests">
                    <h4>Accepted</h4>
                    <?php if (count($acceptedJoinRequests) > 0): ?>
                        <?php foreach ($acceptedJoinRequests as $request): ?>
                            <?php
                            $groupData = $groupController->displayGroupDetails($request->getGroupId());
                            $group = $groupData['group'];
                            $moduleData = $groupData['module'];
                            ?>
                            <div class="mt-3 group-item">
                                <a href="group_info.php?groupId=<?= htmlspecialchars($group->getGroupId()) ?>"
                                    class="group-name text-decoration-none header"><?= htmlspecialchars($group->getGroupName()) ?></a>
                                <span class="module"><?= htmlspecialchars($group->getModuleCode()) ?>,
                                    <?= htmlspecialchars($moduleData->getModuleName($group->getModuleCode())) ?></span>
                                <span class="timestamp"><strong>Accepted At:
                                    </strong><?= htmlspecialchars($request->getReviewedAt()->format('D, d M Y H:i:s')) ?></span>
                                <span
                                    class="member-count"><?= htmlspecialchars($group->getNoOfMembers()) ?>/<?= htmlspecialchars($group->getMaxMembers()) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No requests have been accepted.</p>
                    <?php endif; ?>
                </div>
                <div class="mt-2 rejected-requests">
                    <h4>Rejected</h4>
                    <?php if (count($rejectedJoinRequests) > 0): ?>
                        <?php foreach ($rejectedJoinRequests as $request): ?>
                            <?php
                            $groupData = $groupController->displayGroupDetails($request->getGroupId());
                            $group = $groupData['group'];
                            $moduleData = $groupData['module'];
                            ?>
                            <div class="mt-3 group-item">
                                <a href="group_info.php?groupId=<?= htmlspecialchars($group->getGroupId()) ?>"
                                    class="group-name text-decoration-none header"><?= htmlspecialchars($group->getGroupName()) ?></a>
                                <span class="module"><?= htmlspecialchars($group->getModuleCode()) ?>,
                                    <?= htmlspecialchars($moduleData->getModuleName($group->getModuleCode())) ?></span>
                                <span class="timestamp"><strong>Rejected At:
                                    </strong><?= htmlspecialchars($request->getReviewedAt()->format('D, d M Y H:i:s')) ?></span>
                                <span
                                    class="member-count"><?= htmlspecialchars($group->getNoOfMembers()) ?>/<?= htmlspecialchars($group->getMaxMembers()) ?></span>

                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No requests have been rejected.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="review-wrapper">            
            <?php if ($profileId === $loggedInId): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="filter-group">
                        <label for="filterType">Filter By:</label>
                        <select id="filterType" class="filter-select">
                            <option value="reviewee">Received Reviews</option>
                            <option value="reviewer">Reviews I Made</option>
                        </select>
                    </div>
                <?php endif; ?>
                <?php if ($profileId === $loggedInId): ?>
                    <div id="average" class="average-wrapper d-flex flex-row align-items-center justify-content-center">
                <?php else: ?>
                    <div class="average-wrapper d-flex mb-4 flex-row align-items-center justify-content-center">
                <?php endif; ?>
                    <div class="average-rating text-center">
                        <h3 class="rating-value"><?= htmlspecialchars($averageRating) ?> Stars</h3>
                        <p>on average</p>
                    </div>
                    <div class="d-flex flex-column align-items-center">
                        <div class="rating-stars d-flex flex-row align-items-center mb-1">
                            <?php
                            $fullStars = floor($averageRating);
                            $halfStar = ($averageRating - $fullStars) >= 0.5 ? true : false;
                            $emptyStars = $maxStars - $fullStars - ($halfStar ? 1 : 0);
                            ?>
                            <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                <img src="img/star-filled.svg" alt="Full Star Rating" />
                            <?php endfor; ?>
                            <?php if ($halfStar): ?>
                                <img src="img/star-half.svg" alt="Half Star Rating" />
                            <?php endif; ?>
                            <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                <img src="img/star-unfilled.svg" alt="Empty Star Rating" />
                            <?php endfor; ?>
                        </div>
                        <p class="total-reviews m-0 text-muted small">
                            (<?= htmlspecialchars($totalReviews) . ' ' . ($totalReviews < 2 ? "review" : "reviews") ?>)</p>
                    </div>
                </div>
            <?php if ($profileId === $loggedInId): ?>
            </div>
            <?php endif; ?>
            <div class="reviews-container" id="receivedReviews">
                <?php if (count($receivedReviews) > 0): ?>
                    <?php foreach ($receivedReviews as $review): ?>
                        <div class="review-item mb-4">
                            <div class="top-wrapper d-flex flex-row align-items-center">
                                <div class="rating-stars d-flex flex-row align-items-center">
                                    <?php
                                    $rating = $review->getRating();
                                    $fullStars = floor($rating);
                                    $halfStar = ($rating - $fullStars) >= 0.5 ? true : false;
                                    $emptyStars = floor(5 - ($rating));
                                    ?>
                                    <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                        <img src="img/star-filled.svg" alt="Full Star Rating" />
                                    <?php endfor; ?>
                                    <?php if ($halfStar): ?>
                                        <img src="img/star-half.svg" alt="Half Star Rating" />
                                    <?php endif; ?>
                                    <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                        <img src="img/star-unfilled.svg" alt="Empty Star Rating" />
                                    <?php endfor; ?>
                                </div>
                                <div class="timestamp">
                                    <span
                                        class="text-muted small"><?= htmlspecialchars($review->getReviewDate()->format('D, d M Y H:i:s')) ?></span>
                                </div>
                            </div>
                            <div class="review-content">
                                <div class="review-group-name mt-2">
                                    <?php
                                    $groupId = $groupMembershipController->displayGroupId($review->getGroupMembersId());
                                    $groupData = $groupController->displayGroupDetails($groupId);
                                    $group = $groupData['group'];
                                    $moduleData = $groupData['module'];
                                    ?>
                                    <a href="group_info.php?groupId=<?= $groupId ?>"
                                        class="group-name header text-decoration-none"><strong><?= htmlspecialchars($group->getGroupName()) ?></strong></a>
                                </div>
                                <div class="review-text mt-2">
                                    <p class="mb-0"><?= htmlspecialchars($review->getReviewDescription()) ?></p>
                                </div>
                                <?php
                                $hasReply = $replyController->checkIfReplyExists($review->getReviewId());
                                if ($hasReply):
                                    $reply = $replyController->onViewReply($review->getReviewId());
                                    ?>
                                    <div class="reply-section borderline mt-3">
                                        <div class="reply-title">
                                            <strong>Reply</strong>
                                            <span
                                                class="text-muted small">(<?= htmlspecialchars($reply->getReplyDate()->format('D, d M Y H:i:s')) ?>)</span>
                                        </div>
                                        <div class="reply-text">
                                            <p class="mb-0"><?= htmlspecialchars($reply->getJustification()) ?></p>
                                        </div>
                                    </div>
                                <?php elseif ($student->getStudentId() === $loggedInId): ?>
                                    <div class="reply-section mt-3">
                                        <form action="process_reply_create.php" method="post">
                                            <input type="hidden" name="review_id"
                                                value="<?= htmlspecialchars($review->getReviewId()) ?>">
                                            <input type="hidden" name="reviewer_id"
                                                value="<?= htmlspecialchars($review->getReviewerId()) ?>">
                                            <input type="hidden" name="responder_id"
                                                value="<?= htmlspecialchars($review->getRevieweeId()) ?>">
                                            <div class="d-flex flex-row align-items-center justify-content-around">
                                                <textarea name="justification" class="form-control m-0" placeholder="Enter a reply"
                                                    rows="1" required=""></textarea>
                                                <button type="submit"
                                                    class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center mx-4">Reply</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No reviews yet.</p>
                <?php endif; ?>
            </div>
            <?php if ($profileId === $loggedInId): ?>
            <div class="reviews-container d-none" id="myReviews">
                <?php if (count($myReviews) > 0): ?>
                    <?php foreach ($myReviews as $review): ?>
                        <div class="review-item mb-4">
                            <div class="top-wrapper d-flex flex-row align-items-center">
                                <div class="rating-stars d-flex flex-row align-items-center">
                                    <?php
                                    $rating = $review->getRating();
                                    $fullStars = floor($rating);
                                    $halfStar = ($rating - $fullStars) >= 0.5 ? true : false;
                                    $emptyStars = floor(5 - ($rating));
                                    ?>
                                    <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                        <img src="img/star-filled.svg" alt="Full Star Rating" />
                                    <?php endfor; ?>
                                    <?php if ($halfStar): ?>
                                        <img src="img/star-half.svg" alt="Half Star Rating" />
                                    <?php endif; ?>
                                    <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                        <img src="img/star-unfilled.svg" alt="Empty Star Rating" />
                                    <?php endfor; ?>
                                </div>
                                <div class="timestamp">
                                    <span
                                        class="text-muted small"><?= htmlspecialchars($review->getReviewDate()->format('D, d M Y H:i:s')) ?></span>
                                </div>
                            </div>
                            <div class="review-content">
                                <div class="review-group-name mt-2">
                                    <?php
                                    $groupId = $groupMembershipController->displayGroupId($review->getGroupMembersId());
                                    $groupData = $groupController->displayGroupDetails($groupId);
                                    $group = $groupData['group'];
                                    $moduleData = $groupData['module'];
                                    ?>
                                    <a href="group_info.php?groupId=<?= $groupId ?>"
                                        class="group-name header text-decoration-none"><strong><?= htmlspecialchars($group->getGroupName()) ?></strong></a>
                                </div>
                                <div class="review-text mt-2">
                                    <p class="mb-0"><?= htmlspecialchars($review->getReviewDescription()) ?></p>
                                </div>
                                <?php
                                $hasReply = $replyController->checkIfReplyExists($review->getReviewId());
                                if ($hasReply):
                                    $reply = $replyController->onViewReply($review->getReviewId());
                                    ?>
                                    <div class="reply-section borderline mt-3">
                                        <div class="reply-title">
                                            <strong>Reply</strong>
                                            <span
                                                class="text-muted small">(<?= htmlspecialchars($reply->getReplyDate()->format('D, d M Y H:i:s')) ?>)</span>
                                        </div>
                                        <div class="reply-text">
                                            <p class="mb-0"><?= htmlspecialchars($reply->getJustification()) ?></p>
                                        </div>
                                    </div>
                                
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No reviews made.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if ($profileId === $loggedInId): ?>
    <script src="js/profile.js"></script>
<?php endif; ?>
<!-- Page-specific content ends -->

<?php
$content = ob_get_clean();
include '_layout.php';
?>