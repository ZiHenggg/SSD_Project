<?php
namespace App\Boundary;

use App\Entity\Reply;
use App\Control\ReplyControl;
use App\Control\ReviewControl;
use Exception;

class ReplyPageController
{
    private ReplyControl $replyControl;
    private ReviewControl $reviewControl;

    public function __construct(ReplyControl $replyControl, ReviewControl $reviewControl)
    {
        $this->replyControl = $replyControl;
        $this->reviewControl = $reviewControl;
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

    public function onSubmitReply(int $reviewId, int $responderId, string $justification): void
    {
        if (empty($justification)) {
            throw new Exception("Justification cannot be empty.");
        }

        if ($this->replyControl->hasUserReplied($reviewId)) {
            throw new Exception("You have already replied to this review.");
        }

        try {
            $this->replyControl->submitReply($reviewId, $responderId, $justification);
        } catch (Exception $e) {
            throw new Exception('An error occurred while submitting the reply: ' . $e->getMessage());
        }
    }

}
?>