<?php
namespace App\Control;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use App\Repository\StudentStatsRepository;

class ReviewControl
{
    private ReviewRepository $reviewRepo;
    private StudentStatsRepository $studentStatsRepo;

    public function __construct(
        ReviewRepository $reviewRepo,
        StudentStatsRepository $studentStatsRepo
    ) {
        $this->reviewRepo = $reviewRepo;
        $this->studentStatsRepo = $studentStatsRepo;
    }

    public function getReviewsByReviewee(int $studentId): array
    {
        return $this->reviewRepo->getReviewsForReviewee($studentId);
    }

    public function getReviewsByReviewer ($studentId): array
    {
        return $this->reviewRepo->getReviewsByReviewer($studentId);
    }
     
    public function hasUserReviewed(int $reviewerId, int $revieweeId, int $groupId): bool
    {
        return $this->reviewRepo->hasUserReviewed($reviewerId, $revieweeId, $groupId);
    }

    public function submitReview(int $reviewerId, int $revieweeId, int $groupId, int $rating, string $description): void {
        $groupMembersId = $this->reviewRepo->resolveGroupMembersId($reviewerId, $revieweeId, $groupId);
        
        $review = new Review(
            null,
            $reviewerId,
            $revieweeId,
            $groupMembersId,
            $rating,
            $description,
            date('Y-m-d H:i:s')
        );

        $this->reviewRepo->addReview($review);
    }

    public function calculateAverageRating(int $studentId): float
    {
        return $this->studentStatsRepo->getAverageRating($studentId);
    }

    public function getTotalReviews(int $studentId): int
    {
        return $this->studentStatsRepo->getTotalReviews($studentId);
    }

    public function getReviewById(int $reviewId): ?Review
    {
        return $this->reviewRepo->getReview($reviewId);
    }

    public function containsProfanity(string $description): bool
    {
        // Load the profanity list from JSON
        $profanities = json_decode(file_get_contents(__DIR__ . '/../words.json'), true);

        // Loop through each profanity and check if it appears in the description (case-insensitive)
        foreach ($profanities as $badWord) {
            if (stripos($description, $badWord) !== false) {
                return true;
            }
        }
        return false;
    }

}
?>