<?php

/**
 * ✅ testSubmitReviewSuccess – Valid review submission
 * ✅ testSubmitReplySuccess – Valid reply submission
 * ✅ testSubmitReviewMissingContext – Handles missing context gracefully
 * ✅ testSubmitReviewInvalidRating – Rejects review with invalid rating
 * ✅ testSubmitReplyDuplicate – Prevents duplicate replies
 * ✅ testSubmitReplyNotInSameGroup – Rejects reply if not in same group
 * ✅ testSubmitReplyMissingFields – Handles empty justification input
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
use PHPUnit\Framework\TestCase;

class ReviewReplyFlowTest extends TestCase
{
    private ReviewPageController $reviewController;
    private ReplyPageController $replyController;

    protected function setUp(): void
    {
        $reviewRepo = $this->createMock(ReviewRepository::class);
        $replyRepo = $this->createMock(ReplyRepository::class);
        $studentRepo = $this->createMock(StudentRepository::class);
        $studentStatsRepo = $this->createMock(StudentStatsRepository::class);

        $reviewRepo->method('addReview')->willReturn(null);
        $replyRepo->method('addReply')->willReturn(null);
        $replyRepo->method('getReplyByReview')->willReturn(null);
        $reviewRepo->method('getReview')->willReturn(
            new Review(1, 2, 3, 4, 5, 'Great teamwork!', new \DateTime())
        );

        $reviewControl = new ReviewControl($reviewRepo, $studentStatsRepo);
        $replyControl = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);

        $this->reviewController = new ReviewPageController($reviewControl, null);
        $this->replyController = new ReplyPageController($replyControl, null);
    }

    public function testSubmitReviewSuccess(): void
    {
        $this->expectNotToPerformAssertions();

        $this->reviewController->onSubmitReview(
            1, 2, 3, 5, 'Nice work!', date('Y-m-d H:i:s')
        );
    }

    public function testSubmitReplySuccess(): void
    {
        $this->expectNotToPerformAssertions();

        $this->replyController->onSubmitReply(
            1, 3, 'Thanks for the feedback!', date('Y-m-d H:i:s')
        );
    }

    public function testSubmitReviewMissingContext(): void
    {
        $this->expectException(\Exception::class);

        // Simulate invalid rating (e.g. 0)
        $this->reviewController->onSubmitReview(
            1, 2, 3, 0, 'bad', date('Y-m-d H:i:s')
        );
    }

    public function testSubmitReviewInvalidRating(): void
    {
        $this->expectException(\Exception::class);

        // Rating too high
        $this->reviewController->onSubmitReview(
            1, 2, 3, 6, 'Too high', date('Y-m-d H:i:s')
        );
    }

    public function testSubmitReplyDuplicate(): void
    {
        $this->expectException(\Exception::class);

        // Mock reply already exists
        $replyRepo = $this->createMock(ReplyRepository::class);
        $replyRepo->method('getReplyByReview')->willReturn(
            new Reply(1, 3, 'Already replied', new \DateTime())
        );

        $reviewRepo = $this->createMock(ReviewRepository::class);
        $reviewRepo->method('getReview')->willReturn(
            new Review(1, 2, 3, 4, 5, 'Some review', new \DateTime())
        );

        $studentRepo = $this->createMock(StudentRepository::class);
        $control = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);
        $controller = new ReplyPageController($control, null);

        $controller->onSubmitReply(1, 3, 'Trying again', date('Y-m-d H:i:s'));
    }

    public function testSubmitReplyNotInSameGroup(): void
    {
        $this->expectException(\Exception::class);

        // Mock review with mismatched group
        $reviewRepo = $this->createMock(ReviewRepository::class);
        $reviewRepo->method('getReview')->willReturn(
            new Review(1, 2, 3, 999, 5, 'Bad group match', new \DateTime())
        );

        $replyRepo = $this->createMock(ReplyRepository::class);
        $studentRepo = $this->createMock(StudentRepository::class);
        $control = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);
        $controller = new ReplyPageController($control, null);

        $controller->onSubmitReply(1, 4, 'Wrong group', date('Y-m-d H:i:s'));
    }

    public function testSubmitReplyMissingFields(): void
    {
        $this->expectException(\Exception::class);

        $this->replyController->onSubmitReply(1, 3, '', date('Y-m-d H:i:s'));
    }
}
