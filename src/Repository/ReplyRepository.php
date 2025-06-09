<?php
namespace App\Repository;
use App\Entity\Reply; 

interface ReplyRepository {
    
    // public function addReply(Reply $reply): void;
    public function getReplyByReviewId(int $reviewId): ?Reply;
    public function hasReply(int $reviewId): bool;
}
?>