<?php
namespace App\Control;

use App\Entity\Reply;
use App\Repository\ReplyRepository;
use App\Repository\ReviewRepository;
use App\Repository\StudentRepository;

class ReplyControl
{
    private ReplyRepository $replyRepo;
    private ReviewRepository $reviewRepo;
    private StudentRepository $studentRepo;

    public function __construct(
        ReplyRepository $replyRepo,
        ReviewRepository $reviewRepo,
        StudentRepository $studentRepo
    ) {
        $this->replyRepo = $replyRepo;
        $this->reviewRepo = $reviewRepo;
        $this->studentRepo = $studentRepo;
    }

    public function getReplyForReview(int $reviewId): Reply
    {
        $reply = $this->replyRepo->getReplyByReviewId($reviewId);
        if (!$reply) {
            throw new \Exception("Reply not found for review ID: $reviewId");
        }
        return $reply;
    }
    
    public function hasUserReplied(int $reviewId): bool
    {
        return $this->replyRepo->hasReply($reviewId);
    }

    public function submitReply(int $reviewId, int $responderId, string $justification): void
    {
        if (!$this->studentRepo->getStudentById($responderId)) {
            throw new \Exception("Invalid responder ID: $responderId");
        }

        $review = $this->reviewRepo->getReview($reviewId);
        if (!$review) {
            throw new \Exception("Review not found with ID: $reviewId");
        }

        if ($this->replyRepo->hasReply($reviewId)) {
            throw new \Exception("You have already replied to this review.");
        }

        // Optional: group check, e.g. same group logic here

        $reply = new Reply(
            null,
            $reviewId,
            $responderId,
            $justification,
            date('Y-m-d H:i:s')
        );

        $this->replyRepo->addReply($reply);
    }

}
?>