<?php
namespace App\Boundary;

use App\Entity\Reply;
use App\Control\ReplyControl;
use PDO;
use Exception;

class ReplyPageController
{
    private ReplyControl $replyControl;
    private PDO $pdo;

    public function __construct(ReplyControl $replyControl, PDO $pdo)
    {
        $this->replyControl = $replyControl;
        $this->pdo = $pdo;
    }

    public function onViewReply(int $reviewId): Reply
    {
        try {
            $reply = $this->replyControl->getReplyForReview($reviewId);
            return $reply;
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching the reply: ' . $e->getMessage());
        }
    }

    public function checkIfReplyExists(int $reviewId): bool
    {
        try {
            return $this->replyControl->hasUserReplied($reviewId);
        } catch (Exception $e) {
            throw new Exception('An error occurred while checking for reply: ' . $e->getMessage());
        }
    }

}
?>