<?php
namespace App\Control;

use App\Entity\Review;
use App\Repository\ReviewRepository;

class ReviewControl
{
    private ReviewRepository $reviewRepo;
    private int $noOfReviews;
    private array $reviews;

    public function __construct(
        ReviewRepository $reviewRepo
    ) {
        $this->reviewRepo = $reviewRepo;
        $this->noOfReviews = 0;
        $this->reviews = [];
    }

    public function getReviewsByUser(int $userId): array
    {
        $this->reviews = $this->reviewRepo->getReviewsForReviewee($userId);
        $this->noOfReviews = count($this->reviews);
        return $this->reviews;
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
}
?>