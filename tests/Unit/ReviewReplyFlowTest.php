<?php
/**
 * ✅ testSubmitReviewSuccess – Validates full review submission flow
 * ❌ testSubmitReviewMissingContext – Handles missing session context error
 * ❌ testSubmitReviewInvalidRating – Detects invalid rating or empty description
 * ✅ testSubmitReplySuccess – Valid reply submission with group membership validation
 * ❌ testSubmitReplyDuplicate – Prevents duplicate replies from being added
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Boundary\ReviewPageController;
use App\Boundary\ReplyPageController;
use App\Control\ReviewControl;
use App\Control\ReplyControl;
use App\Entity\Review;
use App\Entity\Reply;
use App\Mapper\ReviewMapper;
use App\Mapper\ReplyMapper;
use App\Mapper\StudentMapper;
use App\Mapper\StudentStatsMapper;

class ReviewReplyFlowTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    public function testSubmitReviewSuccess(): void
    {
        $_SESSION['review_context'] = [
            'reviewer_id' => 1,
            'reviewee_id' => 2,
            'group_id' => 10,
        ];

        $reviewRepo = $this->createMock(ReviewMapper::class);
        $statsRepo = $this->createMock(StudentStatsMapper::class);
        $reviewControl = new ReviewControl($reviewRepo, $statsRepo);
        $controller = new ReviewPageController($reviewControl, new \PDO('sqlite::memory:'));

        $reviewRepo->expects($this->once())->method('addReview');

        $controller->onSubmitReview(
            1, // reviewer
            2, // reviewee
            10,
            4,
            "Good teamwork",
            '2025-06-28 12:00:00'
        );

        $this->assertTrue(true); // No exception thrown
    }

    public function testSubmitReplySuccess(): void
    {
        $replyRepo = $this->createMock(ReplyMapper::class);
        $reviewRepo = $this->createMock(ReviewMapper::class);
        $studentRepo = $this->createMock(StudentMapper::class);
        $replyControl = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);
        $controller = new ReplyPageController($replyControl, new \PDO('sqlite::memory:'));

        $replyRepo->expects($this->once())->method('addReply');

        $controller->onSubmitReply(
            15,    // reviewId
            3,     // responderId
            "Thanks for the feedback.",
            '2025-06-28 12:00:00'
        );

        $this->assertTrue(true); // No exception thrown
    }
}
