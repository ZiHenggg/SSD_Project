<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

use Ngmin\Ict2216G5\Concrete\ReviewRepoImpl;
use Ngmin\Ict2216G5\Concrete\ReplyRepoImpl;

// Initialize ReviewRepository
$reviewRepo = new ReviewRepoImpl($pdo);

// Initialize ReplyRepository
$ReplyRepo = new ReplyRepoImpl($pdo);

// Fetch all reviews for the logged-in user
$reviews = $reviewRepo->getReviewsForReviewee($_SESSION['user']['id']);

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
                <p><strong>Full Name:</strong> <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
                <p><strong>Student ID:</strong> <?= htmlspecialchars($_SESSION['user']['id']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user']['email']) ?></p>
            </div>

            <div class="mt-5 pending-requests">
                <h3>Pending Requests</h3>
                <div class="mt-3 group-item">
                    <span class="group-name header">[24/25 T3]-ICT2216-P1-G4</span>
                    <span class="module">ICT2116, Secure Software Development</span>
                    <span class="acad-term">[24/25 T3]</span>
                    <span class="member-count">3/7</span>
                </div>
                <div class="mt-3 group-item">
                    <span class="group-name header">[24/25 T3]-ICT2216-P1-G4</span>
                    <span class="module">ICT2116, Secure Software Development</span>
                    <span class="acad-term">[24/25 T3]</span>
                    <span class="member-count">3/7</span>
                </div>
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
                                    <p class="mb-0"><strong>group still hardcoded [24/25 T3]-ICT2216-P1-G4</strong></p>
                                </div>
                                <div class="review-text mt-2">
                                    <p class="mb-0"><?= htmlspecialchars($review->getReviewDescription()) ?></p>
                                </div>
                                <div class="reply-section borderline mt-3">
                                    <div class="reply-title"><strong>You replied:</strong></div>
                                    <div class="reply-text">
                                        <p class="mb-0">reply still hardcoded Thank you for the kind words! I enjoyed working with you as well.</p>
                                    </div>
                                </div>
                                <!-- <div class="reply-section mt-3">
                                    <a href="#" class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                        Reply
                                    </a>
                                </div> -->
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