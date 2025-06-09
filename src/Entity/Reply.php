<?php
namespace App\Entity;

use DateTime;

class Reply {
    private int $replyId;
    private int $reviewId;
    private string $justification;
    private DateTime $replyDate;

    // Getters
    public function getReplyId(): int {
        return $this->replyId;
    }
    public function getReviewId(): int {
        return $this->reviewId;
    }
    public function getJustification(): string {
        return $this->justification;
    }
    public function getReplyDate(): DateTime {
        return $this->replyDate;
    }

    // Setters
    private function setReplyId(int $replyId): void {
        $this->replyId = $replyId;
    }
    private function setReviewId(int $reviewId): void {
        $this->reviewId = $reviewId;
    }
    private function setJustification(string $justification): void {
        $this->justification = $justification;
    }
    private function setReplyDate(DateTime $replyDate): void {
        $this->replyDate = $replyDate;
    }

    // Constructor
    public function __construct(int $replyId, int $reviewId, string $justification, string $replyDate) {
        $this->replyId = $replyId;
        $this->reviewId = $reviewId;
        $this->justification = $justification;
        $this->replyDate = new DateTime($replyDate);  // Convert string to DateTime object
    }
}
?>
