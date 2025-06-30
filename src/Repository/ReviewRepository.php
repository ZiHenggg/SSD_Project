<?php
namespace App\Repository;

use App\Entity\Review;

interface ReviewRepository
{
    public function addReview(Review $review): void;
    public function getReview(int $reviewId): ?Review;
    public function getReviewsForReviewee(int $studentId): array;
    public function getReviewsByReviewer(int $studentId): array;
    public function hasUserReviewed(int $reviewerId, int $revieweeId, int $groupId): bool;
    public function resolveGroupMembersId(int $reviewerId, int $revieweeId, int $groupId): ?int;
}
?>