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
            SELECT COUNT(*) FROM reviews rev
            INNER JOIN groupmembers gm ON gm.groupMembersId = rev.groupMembersId
            WHERE rev.reviewerId = :reviewerId 
            AND rev.revieweeId = :revieweeId 
            AND gm.groupId = :groupId
        ");
        $stmt->bindParam(':reviewerId', $reviewerId, PDO::PARAM_INT);
        $stmt->bindParam(':revieweeId', $revieweeId, PDO::PARAM_INT);
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function addReview(Review $review): void {
        try {
            $this->dbConnection->beginTransaction();
            
            // INSERT review
            $stmt = $this->dbConnection->prepare("
                INSERT INTO reviews (reviewerId, revieweeId, groupMembersId, reviewRating, description)
                VALUES (:reviewerId, :revieweeId, :groupMembersId, :reviewRating, :description)
            ");

            $reviewerId = $review->getReviewerId();
            $revieweeId = $review->getRevieweeId();
            $groupId = $review->getGroupMembersId();
            $rating = $review->getRating();
            $description = $review->getReviewDescription();

            $stmt->bindParam(':reviewerId', $reviewerId, PDO::PARAM_INT);
            $stmt->bindParam(':revieweeId', $revieweeId, PDO::PARAM_INT);
            $stmt->bindParam(':groupMembersId', $groupId, PDO::PARAM_INT);
            $stmt->bindParam(':reviewRating', $rating, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new \Exception("Failed to add review: " . implode(", ", $stmt->errorInfo()));
            }

            // Ensure studentStats row exists
            $this->dbConnection->prepare("
                INSERT INTO studentStats (studentId, totalReviews, averageRating)
                VALUES (:revieweeId, 0, 0.0)
                ON DUPLICATE KEY UPDATE studentId = studentId
            ")->execute([':revieweeId' => $revieweeId]);

            // Update stats
            $updateStats = $this->dbConnection->prepare("
                UPDATE studentStats
                SET 
                    totalReviews = totalReviews + 1,
                    averageRating = (
                        SELECT ROUND(AVG(reviewRating), 2)
                        FROM reviews
                        WHERE revieweeId = :revieweeId
                    )
                WHERE studentId = :revieweeId
            ");
            $updateStats->bindParam(':revieweeId', $revieweeId, PDO::PARAM_INT);

            if (!$updateStats->execute()) {
                throw new \Exception("Failed to update student stats: " . implode(", ", $updateStats->errorInfo()));
            }

            $this->dbConnection->commit(); 
        } catch (\Exception $e) {
            $this->dbConnection->rollBack(); 
            throw $e;
        }
    }

} 
?>
