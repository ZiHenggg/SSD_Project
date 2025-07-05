<?php
$pageControllers = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth_check.php';
require_once __DIR__ . '/../src/CsrfManager.php';

use App\SessionManager;

// CSRF check before doing anything else
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfManager::validateToken($_POST['csrf_token'] ?? '')) {
        SessionManager::destroy();
        header('Location: error.php');
        exit;
    }
}

$context = SessionManager::getReviewContext();
if (!$context) {
    SessionManager::setError("Missing review context.");
    header("Location: dashboard.php");
    exit;
}

$reviewerId = $context['reviewer_id'];
$revieweeId = $context['reviewee_id'];
$groupId = $context['group_id'];

$rating = (int) ($_POST['rating'] ?? 0);
$description = trim($_POST['description'] ?? '');

if ($rating < 1 || $rating > 5 || empty($description)) {
    SessionManager::setError("Invalid input. Please fill out all fields correctly.");
    header("Location: review_create.php");
    exit;
}


$reviewController = $pageControllers['reviewPageController'];

try {
    $reviewController->onSubmitReview(
        $reviewerId,
        $revieweeId,
        $groupId,
        $rating,
        $description,
        date('Y-m-d H:i:s')
    );
    
    SessionManager::clearReviewContext(); // Clear context after successful submission
    SessionManager::setSuccess("Review submitted successfully.");
    header("Location: dashboard.php");
    exit;
} catch (Exception $e) {
    SessionManager::setError($e->getMessage());
    header("Location: review_create.php");
    exit;
}


?>