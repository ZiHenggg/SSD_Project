<?php
namespace App\Mapper;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use PDO;

class ReviewMapper implements ReviewRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getReviewsForReviewee(int $studentId): array {
        $stmt = $this->dbConnection->prepare("
            SELECT rev.reviewId, rev.reviewerId, rev.revieweeId, rev.groupMembersId, rev.`description`, rev.reviewRating, rev.reviewDate, 
                        rep.replyId
            FROM reviews rev
            LEFT JOIN reply rep ON rev.reviewId = rep.reviewId 
            WHERE rev.revieweeId = :studentId
        ");
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        $reviewObjects = [];
        foreach ($reviews as $review) {            
            $reviewObj = new Review(
                $review['reviewId'],
                $review['reviewerId'],
                $review['revieweeId'],
                $review['groupMembersId'],
                $review['reviewRating'],
                $review['description'],
                $review['reviewDate']
            );

            $reviewObjects[] = $reviewObj;
        }

        return $reviewObjects;
    }

    public function hasUserReviewed(int $reviewerId, int $revieweeId, int $groupId): bool {
        $stmt = $this->dbConnection->prepare("
            select count(*) from reviews
            inner join groupmembers on
            studentId = reviewerId where reviewerId = :reviewerId and revieweeId = :revieweeId and groupId = :groupId;
        ");
        $stmt->bindParam(':reviewerId', $reviewerId, PDO::PARAM_INT);
        $stmt->bindParam(':revieweeId', $revieweeId, PDO::PARAM_INT);
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }
} 
?>
