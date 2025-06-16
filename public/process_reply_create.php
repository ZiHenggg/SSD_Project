<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\Mapper\ReplyMapper;
use App\Mapper\ReviewMapper;
use App\Mapper\StudentMapper;
use App\Mapper\GroupMapper;
use App\Mapper\GroupMembershipMapper;
use App\Mapper\GroupJoinRequestsMapper;
use App\Control\ReplyControl;
use App\Control\GroupMembershipControl;
use App\Boundary\ReplyPageController;
use App\Boundary\GroupMembershipController;

$replyRepo = new ReplyMapper($pdo);
$reviewRepo = new ReviewMapper($pdo);
$studentRepo = new StudentMapper($pdo);
$groupMembershipRepo = new GroupMembershipMapper($pdo);
$groupRepo = new GroupMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);
$replyControl = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);
$replyController = new ReplyPageController($replyControl, $pdo);
$groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
$groupMembershipController = new GroupMembershipController($groupMembershipControl, $pdo);

// Session check
$loggedInId = $_SESSION['user']['id'] ?? 0;

// Input sanitisation
$reviewId = (int) ($_POST['review_id'] ?? 0);
$reviewerId = (int) ($_POST['reviewer_id'] ?? 0);
$responderId = (int) ($_POST['responder_id'] ?? 0);
$justification = trim($_POST['justification'] ?? '');

try {
    // Basic input validation
    if (
        $reviewId <= 0 ||
        $reviewerId <= 0 ||
        $responderId !== $loggedInId ||
        empty($justification)
    ) {
        throw new Exception("Invalid submission.");
    }

    // Check if review exists
    $review = $reviewRepo->getReview($reviewId);
    if (!$review) {
        throw new Exception("Something went wrong. Please try again.");
    }

    $groupId = $groupMembershipController->displayGroupId($review->getGroupMembersId());

    // Check both reviewer and responder are in the same group
    if (!$groupMembershipController->OnCheckIfMember($groupId, $reviewerId) || !$groupMembershipController->OnCheckIfMember($groupId, $responderId)) {
        throw new Exception("Something went wrong. Please try again.");
    }

    // Prevent duplicate reply
    if ($replyController->checkIfReplyExists($reviewId)) {
        throw new Exception("You have already replied to this review.");
    }

    // Submit reply
    $replyController->onSubmitReply($reviewId, $responderId, $justification, date('Y-m-d H:i:s'));
    $_SESSION['success'] = "Reply submitted successfully.";

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: profile.php");
exit;
