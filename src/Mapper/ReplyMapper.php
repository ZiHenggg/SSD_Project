<?php
namespace App\Mapper;

use App\Entity\Reply;
use App\Repository\ReplyRepository;
use PDO;

class ReplyMapper implements ReplyRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getReplyByReviewId(int $reviewId): ?Reply {
        $stmt = $this->dbConnection->prepare("SELECT replyId, responderId, justification, replyDate FROM reply WHERE reviewId = :reviewId");
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->execute();

        $replyData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($replyData) {            
            return new Reply(
                $replyData['replyId'],
                $reviewId,
                $replyData['responderId'],
                $replyData['justification'],
                $replyData['replyDate']
            );
        }

        return null; // No reply found
    }

    public function hasReply(int $reviewId): bool {
        $stmt = $this->dbConnection->prepare("SELECT COUNT(*) FROM reply WHERE reviewId = :reviewId");
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    public function addReply(Reply $reply): void {
        $stmt = $this->dbConnection->prepare("INSERT INTO reply (reviewId, responderId, justification) VALUES (:reviewId, :responderId, :justification)");
        
        $reviewId = $reply->getReviewId();
        $responderId = $reply->getResponderId();
        $justification = $reply->getJustification();

        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->bindParam(':responderId', $responderId, PDO::PARAM_INT);
        $stmt->bindParam(':justification', $justification, PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            throw new \Exception("Failed to add reply: " . implode(", ", $stmt->errorInfo()));
        }
    }
} 
?>
