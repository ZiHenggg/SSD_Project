<?php

/**
 * ✅ testSubmitReviewSuccess – Simulates a valid review submission flow
 * ✅ testSubmitReplySuccess – Valid reply with justification from same group
 * ✅ testSubmitReviewMissingContext – Handles missing review context in session
 * ✅ testSubmitReviewInvalidRating – Blocks review if rating/description invalid
 * ✅ testSubmitReplyDuplicate – Prevents reply to already replied review
 * ✅ testSubmitReplyNotInSameGroup – Rejects reply if not same group
 * ✅ testSubmitReplyMissingFields – Blocks empty or invalid reply submissions
 */

namespace Tests\Unit;

use App\Boundary\ReplyPageController;
use App\Boundary\ReviewPageController;
use App\Control\ReplyControl;
use App\Control\ReviewControl;
use App\Entity\Review;
use App\Entity\Reply;
use App\Repository\ReviewRepository;
use App\Repository\ReplyRepository;
use App\Repository\StudentRepository;
use App\Repository\StudentStatsRepository;
use App\SessionManager;
use PHPUnit\Framework\TestCase;

class ReviewReplyFlowTest extends TestCase
{
    private $session;
    private $reviewRepo;
    private $replyRepo;
    private $studentRepo;
    private $studentStatsRepo;
    private $reviewController;
    private $replyController;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionManager::class);
        $this->reviewRepo = $this->createMock(ReviewRepository::class);
        $this->replyRepo = $this->createMock(ReplyRepository::class);
        $this->studentRepo = $this->createMock(StudentRepository::class);
        $this->studentStatsRepo = $this->createMock(StudentStatsRepository::class);

        $reviewControl = new ReviewControl($this->reviewRepo, $this->studentStatsRepo);
        $replyControl = new ReplyControl($this->replyRepo, $this->reviewRepo, $this->studentRepo);

        $this->reviewController = new ReviewPageController($reviewControl);
        $this->replyController = new ReplyPageController($replyControl);
    }

    public function testSubmitReviewSuccess(): void
    {
        $this->reviewRepo->method('addReview')->willReturn(true);

        $result = $this->reviewController->onSubmitReview(
            reviewerId: 1,
            revieweeId: 2,
            groupId: 1,
            rating: 4,
            description: 'Good teammate',
            timestamp: date('Y-m-d H:i:s')
        );

        $this->assertNull($result); // If no exception thrown, it's successful
    }

    public function testSubmitReplySuccess(): void
    {
        $review = new Review(1, 1, 2, 3, 'Great', 5, new \DateTime());
        $this->reviewRepo->method('getReview')->willReturn($review);
        $this->replyRepo->method('addReply')->willReturn(true);
        $this->replyRepo->method('checkIfReplyExists')->willReturn(false);
        $this->studentRepo->method('isSameGroup')->willReturn(true);

        $this->replyController->onSubmitReply(
            reviewId: 1,
            responderId: 3,
            justification: 'Appreciated!',
            timestamp: date('Y-m-d H:i:s')
        );

        $this->assertTrue(true); // No exceptions thrown
    }

    public function testSubmitReviewMissingContext(): void
    {
        $this->expectException(\Exception::class);
        $this->reviewController->onSubmitReview(
            reviewerId: 0,
            revieweeId: 0,
            groupId: 0,
            rating: 0,
            description: '',
            timestamp: date('Y-m-d H:i:s')
        );
    }

    public function testSubmitReviewInvalidRating(): void
    {
        $this->expectException(\Exception::class);
        $this->reviewController->onSubmitReview(
            reviewerId: 1,
            revieweeId: 2,
            groupId: 1,
            rating: 10,
            description: '',
            timestamp: date('Y-m-d H:i:s')
        );
    }

    public function testSubmitReplyDuplicate(): void
    {
        $this->expectException(\Exception::class);

        $review = new Review(1, 1, 2, 3, 'Hard worker', 5, new \DateTime());
        $this->reviewRepo->method('getReview')->willReturn($review);
        $this->replyRepo->method('checkIfReplyExists')->willReturn(true);
        $this->studentRepo->method('isSameGroup')->willReturn(true);

        $this->replyController->onSubmitReply(
            reviewId: 1,
            responderId: 3,
            justification: 'Already replied',
            timestamp: date('Y-m-d H:i:s')
        );
    }

    public function testSubmitReplyNotInSameGroup(): void
    {
        $this->expectException(\Exception::class);

        $review = new Review(1, 1, 2, 3, 'Helpful', 4, new \DateTime());
        $this->reviewRepo->method('getReview')->willReturn($review);
        $this->replyRepo->method('checkIfReplyExists')->willReturn(false);
        $this->studentRepo->method('isSameGroup')->willReturn(false);

        $this->replyController->onSubmitReply(
            reviewId: 1,
            responderId: 3,
            justification: 'Thanks',
            timestamp: date('Y-m-d H:i:s')
        );
    }

    public function testSubmitReplyMissingFields(): void
    {
        $this->expectException(\Exception::class);

        $review = new Review(1, 1, 2, 3, 'Nice', 4, new \DateTime());
        $this->reviewRepo->method('getReview')->willReturn($review);
        $this->replyRepo->method('checkIfReplyExists')->willReturn(false);
        $this->studentRepo->method('isSameGroup')->willReturn(true);

        $this->replyController->onSubmitReply(
            reviewId: 1,
            responderId: 3,
            justification: '',
            timestamp: date('Y-m-d H:i:s')
        );
    }
}
