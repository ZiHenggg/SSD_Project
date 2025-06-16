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

    public function getReview(int $reviewId): ?Review {
        $stmt = $this->dbConnection->prepare("
            SELECT reviewId, reviewerId, revieweeId, groupMembersId, description, reviewRating, reviewDate
            FROM reviews
            WHERE reviewId = :reviewId
        ");
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->execute();

        $reviewData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reviewData) {
            return null;
        }

        return new Review(
            $reviewData['reviewId'],
            $reviewData['reviewerId'],
            $reviewData['revieweeId'],
            $reviewData['groupMembersId'],
            $reviewData['reviewRating'],
            $reviewData['description'],
            $reviewData['reviewDate']
        );
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
            $groupMembersId = $review->getGroupMembersId();
            $rating = $review->getRating();
            $description = $review->getReviewDescription();

            $stmt->bindParam(':reviewerId', $reviewerId, PDO::PARAM_INT);
            $stmt->bindParam(':revieweeId', $revieweeId, PDO::PARAM_INT);
            $stmt->bindParam(':groupMembersId', $groupMembersId, PDO::PARAM_INT);
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

    public function resolveGroupMembersId(int $reviewerId, int $revieweeId, int $groupId): ?int {
    $stmt = $this->dbConnection->prepare("
        SELECT gm.groupMembersId
        FROM groupmembers gm
        WHERE gm.studentId = :reviewerId
          AND gm.groupId = :groupId
          AND EXISTS (
              SELECT 1 FROM groupmembers gm2
              WHERE gm2.studentId = :revieweeId AND gm2.groupId = :groupId
          )
    ");
    $stmt->bindParam(':reviewerId', $reviewerId, PDO::PARAM_INT);
    $stmt->bindParam(':revieweeId', $revieweeId, PDO::PARAM_INT);
    $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['groupMembersId'] : null;
}

} 
?>
