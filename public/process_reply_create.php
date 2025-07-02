<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use App\SessionManager;

$groupMembershipController = $pageControllers['groupMembershipController'];
$replyController = $pageControllers['replyPageController'];
$reviewPageController = $pageControllers['reviewPageController'];

// Session check
$loggedInId = SessionManager::get('user')['id'] ?? 0;

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
    $review = $reviewPageController->onGetReviewById($reviewId);
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
    SessionManager::setSuccess("Reply submitted successfully.");

} catch (Exception $e) {
    SessionManager::setError($e->getMessage());
}

header("Location: profile.php");
exit;
