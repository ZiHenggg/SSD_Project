<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';

$context = $_SESSION['review_context'] ?? null;
if (!$context) {
    $_SESSION['error'] = "Missing review context.";
    header("Location: dashboard.php");
    exit;
}

$reviewerId = $context['reviewer_id'];
$revieweeId = $context['reviewee_id'];
$groupId = $context['group_id'];

$rating = (int) ($_POST['rating'] ?? 0);
$description = trim($_POST['description'] ?? '');

if ($rating < 1 || $rating > 5 || empty($description)) {
    $_SESSION['error'] = "Invalid input. Please fill out all fields correctly.";
    header("Location: review_create.php");
    exit;
}

use App\Mapper\ReviewMapper;
use App\Mapper\StudentStatsMapper;
use App\Control\ReviewControl;
use App\Boundary\ReviewPageController;

$reviewRepo = new ReviewMapper($pdo);
$studentStatsRepo = new StudentStatsMapper($pdo);
$reviewControl = new ReviewControl($reviewRepo, $studentStatsRepo);
$reviewController = new ReviewPageController($reviewControl, $pdo);

try {
    $reviewController->onSubmitReview(
        $reviewerId,
        $revieweeId,
        $groupId,
        $rating,
        $description,
        date('Y-m-d H:i:s')
    );
    
    unset($_SESSION['review_context']); // Clear context after successful submission
    $_SESSION['success'] = "Review submitted successfully.";
    header("Location: dashboard.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: review_create.php");
    exit;
}


?>