<?php
namespace Ngmin\Ict2216G5\Repository;
use Ngmin\Ict2216G5\Entity\Review; 

interface ReviewRepository {
    
    // public function addReview(Review $review): void;
    
    // public function getReview(int $reviewId): ?Review;

    // public function getReviewsByReviewer(int $studentId): array;

    public function getReviewsForReviewee(int $studentId): array;

    // public function hasUserReviewed(int $reviewerId, int $revieweeId, int $groupId): bool;

    // public function getAllReviews(): array;
}
?>