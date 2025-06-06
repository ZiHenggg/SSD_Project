<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

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
                    <h3 class="rating-value">4.5 Stars</h3>
                    <p>on average</p>
                </div>
                <div class="rating-stars d-flex flex-row align-items-center">
                    <img src="img/star-filled.svg" alt="Star Rating" class="w-100" />
                    <img src="img/star-filled.svg" alt="Star Rating" class="w-100" />
                    <img src="img/star-filled.svg" alt="Star Rating" class="w-100" />
                    <img src="img/star-filled.svg" alt="Star Rating" class="w-100" />
                    <img src="img/star-half.svg" alt="Star Rating" class="w-100" />
                </div>
            </div>
            <div class="reviews-container">
                <div class="review-item mb-4">
                    <div class="top-wrapper d-flex flex-row align-items-center">
                        <div class="rating-stars d-flex flex-row align-items-center">
                            <img src="img/star-filled.svg" alt="Star Rating"/>
                            <img src="img/star-filled.svg" alt="Star Rating"/>
                            <img src="img/star-filled.svg" alt="Star Rating"/>
                            <img src="img/star-filled.svg" alt="Star Rating"/>
                            <img src="img/star-half.svg" alt="Star Rating"/>
                        </div>
                        <div class="timestamp">
                            <span class="text-muted small">Thu, 05 Jun 2025 17:04:09</span>
                        </div>
                    </div>
                    <div class="review-content">
                        <div class="review-group-name mt-2">
                            <p class="mb-0"><strong>[24/25 T3]-ICT2216-P1-G4</strong></p>
                        </div>
                        <div class="review-text mt-2">
                            <p class="mb-0">Very good teammate, easy to work with and will definitely work together again if i get the chance.</p>
                        </div>
                        <div class="reply-section borderline mt-3">
                            <div class="reply-title"><strong>You replied:</strong></div>
                            <div class="reply-text">
                                <p class="mb-0">Thank you for the kind words! I enjoyed working with you as well.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="review-item mb-4">
                    <div class="top-wrapper d-flex flex-row align-items-center">
                        <div class="rating-stars d-flex flex-row align-items-center">
                            <img src="img/star-filled.svg" alt="Star Rating"/>
                            <img src="img/star-filled.svg" alt="Star Rating"/>
                            <img src="img/star-filled.svg" alt="Star Rating"/>
                            <img src="img/star-filled.svg" alt="Star Rating"/>
                            <img src="img/star-half.svg" alt="Star Rating"/>
                        </div>
                        <div class="timestamp">
                            <span class="text-muted small">Thu, 05 Jun 2025 17:04:09</span>
                        </div>
                    </div>
                    <div class="review-content">
                        <div class="review-group-name mt-2">
                            <p class="mb-0"><strong>[24/25 T3]-ICT2216-P1-G4</strong></p>
                        </div>
                        <div class="review-text mt-2">
                            <p class="mb-0">Very good teammate, easy to work with and will definitely work together again if i get the chance.</p>
                        </div>
                            <div class="reply-section mt-3">
                                <a href="#" class="text-decoration-none text-center reply-button d-flex align-items-center justify-content-center">
                                    Reply
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Page-specific content ends -->

<?php
$content = ob_get_clean();
include '_layout.php';
?>