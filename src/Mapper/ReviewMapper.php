<?php
namespace Ngmin\Ict2216G5\Mapper;

use Ngmin\Ict2216G5\Entity\Review;
use Ngmin\Ict2216G5\Repository\ReviewRepository;
use PDO;

class ReviewMapper implements ReviewRepository {

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

        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        $reviewObjects = [];
        foreach ($reviews as $review) {            
            $reviewObj = new Review(
                $review['reviewId'],
                $review['reviewerId'],
                $review['revieweeId'],
                $review['reviewRating'],
                $review['description'],
                $review['reviewDate']
            );

            $reviewObjects[] = $reviewObj;
        }

        return $reviewObjects;
    }
} 
?>
