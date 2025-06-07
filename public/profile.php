<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use Ngmin\Ict2216G5\Mapper\StudentMapper;
use Ngmin\Ict2216G5\Mapper\ReviewMapper;
use Ngmin\Ict2216G5\Mapper\ReplyMapper;
use Ngmin\Ict2216G5\Mapper\GroupMapper;
use Ngmin\Ict2216G5\Mapper\GroupJoinRequestsMapper;

// Initialize Repositories
$studentRepo = new StudentMapper($pdo);
$reviewRepo = new ReviewMapper($pdo);
$groupRepo = new GroupMapper($pdo);
$replyRepo = new ReplyMapper($pdo);
$groupJoinRequestsRepo = new GroupJoinRequestsMapper($pdo);

// !!!!!!!!!!!!!!!!!!!!!!!!
// Fetch info for the logged-in user (update when we implement viewing of other profiles)
$student = $studentRepo->getStudentById($_SESSION['user']['id']);
// !!!!!!!!!!!!!!!!!!!!!!!!

$reviews = $reviewRepo->getReviewsForReviewee($student->getStudentId());
$groupJoinRequests = $groupJoinRequestsRepo->getRequestsByStudent($student->getStudentId());

$totalRating = 0;
$reviewCount = count($reviews);

if ($reviewCount > 0) {
    foreach ($reviews as $review) {
        $totalRating += $review->getRating(); 
    }
    $averageRating = round($totalRating / $reviewCount, 1);
} else {
    $averageRating = 0; 
}

$title = "Profile";
ob_start();
?>

<!-- Page-specific content starts here -->
<div class="profile">
    <h2 class="mb-5">My Profile</h2>
    <div class="content-container d-flex justify-content-between">
        <div class="info-pending-wrapper">
            <div class="profile-info">
                <h3>Info</h3>
                <p><strong>Full Name:</strong> <?= htmlspecialchars($student->getStudentName()) ?></p>
                <p><strong>Student ID:</strong> <?= htmlspecialchars($student->getStudentId()) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($student->getEmail()) ?></p>
            </div>

            <div class="mt-5 pending-requests">
                <h3>Pending Requests</h3>

                <?php if (count($groupJoinRequests) > 0): ?>
                    <?php foreach ($groupJoinRequests as $request): ?>
                        <?php
                            $group = $groupRepo->getGroup($request->getGroupId());
                        ?>
                        <div class="mt-3 group-item">
                            <span class="group-name header"><?= htmlspecialchars($group->getGroupName()) ?></span>
                            <span class="module"><?= htmlspecialchars($group->getModuleCode())?>, TODO: ADD MODULE NAME</span>
                            <span class="acad-term">[<?= htmlspecialchars($group->getAcadYear())?> <?= htmlspecialchars($group->getTrimester())?>]</span>
                            <span class="member-count"><?= htmlspecialchars($group->getNoOfMembers())?>/<?= htmlspecialchars($group->getMaxMembers())?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No pending reviews.</p>
                <?php endif; ?>                   
            </div>
        </div>
            
        <div class="review-wrapper">
            <div class="average-wrapper mb-4 d-flex flex-row align-items-center justify-content-center">
                <div class="average-rating text-center">
                    <h3 class="rating-value"><?= htmlspecialchars($averageRating) ?> Stars</h3>
                    <p>on average</p>
                </div>
                <div class="rating-stars d-flex flex-row align-items-center">
                    <?php
                        $fullStars = floor($averageRating);
                        $halfStar = ($averageRating - $fullStars) >= 0.5 ? true : false;
                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                    ?>
                    <?php for ($i = 0; $i < $fullStars; $i++): ?>
                        <img src="img/star-filled.svg" alt="Full Star Rating"/>
                    <?php endfor; ?>
                    <?php if ($halfStar): ?>
                        <img src="img/star-half.svg" alt="Half Star Rating"/>
                    <?php endif; ?>
                    <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                        <img src="img/star-unfilled.svg" alt="Empty Star Rating"/>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="reviews-container">
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
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
                                        <img src="img/star-filled.svg" alt="Full Star Rating"/>
                                    <?php endfor; ?>
                                    <?php if ($halfStar): ?>
                                        <img src="img/star-half.svg" alt="Half Star Rating"/>
                                    <?php endif; ?>
                                    <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                        <img src="img/star-unfilled.svg" alt="Empty Star Rating"/>
                                    <?php endfor; ?>
                                </div>
                                <div class="timestamp">
                                    <span class="text-muted small"><?= htmlspecialchars($review->getReviewDate()->format('D, d M Y H:i:s')) ?></span>
                                </div>
                            </div>
                            <div class="review-content">
                                <div class="review-group-name mt-2">
                                    <p class="mb-0"><strong>still hardcoded [24/25 T3]-ICT2216-P1-G4</strong></p>
                                </div>
                                <div class="review-text mt-2">
                                    <p class="mb-0"><?= htmlspecialchars($review->getReviewDescription()) ?></p>
                                </div>
                                <?php 
                                    $reply = $replyRepo->getReplyByReviewId($review->getReviewId());
                                    if ($reply):
                                ?> 
                                    <div class="reply-section borderline mt-3">
                                        <div class="reply-title"><strong>Reply:</strong></div>
                                        <div class="reply-text">
                                            <p class="mb-0"><?= htmlspecialchars($reply->getJustification()) ?></p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                <div class="reply-section mt-3">
                                    <a href="#" class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                        Reply
                                    </a>
                                </div>
                                <?php endif; ?>                
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No reviews yet.</p>
                <?php endif; ?>                
            </div>
        </div>
    </div>
</div>
<!-- Page-specific content ends -->

<?php
$content = ob_get_clean();
include '_layout.php';
?>