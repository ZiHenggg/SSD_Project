<?php
namespace Ngmin\Ict2216G5\Concrete;

use Ngmin\Ict2216G5\Entity\Review;
use Ngmin\Ict2216G5\Repository\ReviewRepository;
use PDO;

class ReviewRepoImpl implements ReviewRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getReviewsForReviewee(int $studentId): array {
        $stmt = $this->dbConnection->prepare("
            SELECT rev.reviewId, rev.reviewerId, rev.revieweeId, rev.`description`, rev.reviewRating, rev.reviewDate, 
                        rep.replyId
            FROM reviews rev
            LEFT JOIN reply rep ON rev.reviewId = rep.reviewId 
            WHERE rev.revieweeId = :studentId
        ");
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch reviews as an associative array
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array

        $reviewObjects = [];
        foreach ($reviews as $review) {
            // Create Review objects with constructor injection
            $reviewObj = new Review(
                $review['reviewId'],
                $review['reviewerId'],
                $review['revieweeId'],
                $review['reviewRating'],
                $review['description'],
                $review['reviewDate'] // Pass reviewDate as string to be converted in constructor
            );

            $reviewObjects[] = $reviewObj;
        }

        return $reviewObjects;
    }
} 
?>
