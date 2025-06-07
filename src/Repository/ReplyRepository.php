<?php
namespace Ngmin\Ict2216G5\Repository;
use Ngmin\Ict2216G5\Entity\Reply; 

interface ReplyRepository {
    
    // public function addReply(Reply $reply): void;
    public function getReplyByReviewId(int $reviewId): ?Reply;
    // public function hasReply(int $reviewId): bool;
}
?>