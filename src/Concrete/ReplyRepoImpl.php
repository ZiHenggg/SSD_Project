<?php
namespace Ngmin\Ict2216G5\Concrete;

use Ngmin\Ict2216G5\Entity\Reply;
use Ngmin\Ict2216G5\Repository\ReplyRepository;
use PDO;

class ReplyRepoImpl implements ReplyRepository {

    private PDO $dbConnection;

    public function __construct(PDO $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getReplyByReviewId(int $reviewId): ?Reply {
        $stmt = $this->dbConnection->prepare("SELECT replyId, responderId, justification FROM reply WHERE reviewId = :reviewId");
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the reply as an associative array
        $replyData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($replyData) {
            // Create and return a Reply object
            return new Reply(
                $replyData['replyId'],
                $replyData['responderId'],
                $reviewId,
                $replyData['justification']
            );
        }

        return null; // No reply found for the given review ID
    }
    
} 
?>
