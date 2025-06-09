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
                $replyData['justification'],
                $replyData['replyDate']
            );
        }

        return null; // No reply found
    }
    
} 
?>
