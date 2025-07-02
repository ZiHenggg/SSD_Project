<?php
namespace App\Boundary;

use App\Entity\Review;
use App\Control\ReviewControl;
use PDO;
use Exception;

class ReviewPageController
{
    private ReviewControl $reviewControl;
    private PDO $pdo;

    public function __construct(ReviewControl $reviewControl, PDO $pdo)
    {
        $this->reviewControl = $reviewControl;
        $this->pdo = $pdo;
    }

    public function onViewReceivedReviews(int $studentId): array
    {
        try {
            // Fetch reviews for the user
            $reviews = $this->reviewControl->getReviewsByReviewee($studentId);
            if (empty($reviews)) {
                return ['message' => 'No reviews found for this user.'];
            }
            return ['reviews' => $reviews];
        } catch (Exception $e) {
            return ['error' => 'An error occurred while fetching reviews: ' . $e->getMessage()];
        }
    }

    public function onViewMyReviews(int $studentId): array
    {
        try {
            // Fetch reviews for the user
            $reviews = $this->reviewControl->getReviewsByReviewer($studentId);
            if (empty($reviews)) {
                return ['message' => 'No reviews made by this user.'];
            }
            return ['reviews' => $reviews];
        } catch (Exception $e) {
            return ['error' => 'An error occurred while fetching reviews: ' . $e->getMessage()];
        }
    }

    public function onCheckIfReviewed(int $reviewerId, int $revieweeId, int $groupId): bool
    {
        try {
            return $this->reviewControl->hasUserReviewed($reviewerId, $revieweeId, $groupId);
        } catch (Exception $e) {
            throw new Exception('An error occurred while checking review status: ' . $e->getMessage());
        }
    }

    public function onSubmitReview(int $reviewerId, int $revieweeId, int $groupId, int $rating, string $description): void
    {
        if ($this->onCheckIfReviewed($reviewerId, $revieweeId, $groupId)) {
            throw new Exception("You have already reviewed this user in this group.");
        }
        
        if ($rating < 1 || $rating > 5 || empty($description)) {
            throw new Exception("Invalid input. Please fill out all fields correctly.");
        }

        try {
            $this->reviewControl->submitReview($reviewerId, $revieweeId, $groupId, $rating, $description);
        } catch (Exception $e) {
            throw new Exception('An error occurred while submitting the review: ' . $e->getMessage());
        }
    }

    public function displayReviewStats(int $studentId): array
    {
        try {
            $averageRating = $this->reviewControl->calculateAverageRating($studentId);
            $noOfReviews = $this->reviewControl->getTotalReviews($studentId);
            return ['averageRating' => $averageRating, 'totalReviews' => $noOfReviews];
        } catch (Exception $e) {
            return ['error' => 'An error occurred while calculating average rating: ' . $e->getMessage()];
        }
    }

    public function onGetReviewById(int $reviewId): ?Review
    {        
        return $this->reviewControl->getReviewById($reviewId);  
    }
}
?>