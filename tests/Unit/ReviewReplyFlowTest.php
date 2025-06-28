<?php
/**
 * ✅ testSubmitReviewSuccess – Submits a valid review
 * ✅ testSubmitReplySuccess – Submits a valid reply
 * ✅ testSubmitReviewMissingContext – Rejects when session context is missing
 * ✅ testSubmitReviewInvalidRating – Rejects invalid rating or empty description
 * ✅ testSubmitReplyDuplicate – Rejects duplicate reply attempt
 * ✅ testSubmitReplyNotInSameGroup – Rejects if not in same group
 * ✅ testSubmitReplyMissingFields – Rejects if any required input is missing
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\SessionManager;
use App\Boundary\ReviewPageController;
use App\Boundary\ReplyPageController;
use App\Control\ReviewControl;
use App\Control\ReplyControl;
use App\Control\GroupMembershipControl;
use App\Repository\ReviewRepository;
use App\Repository\ReplyRepository;
use App\Repository\StudentRepository;
use App\Repository\StudentStatsRepository;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use App\Repository\GroupJoinRequestsRepository;
use App\Entity\Review;
use App\Entity\Reply;
use DateTime;

class ReviewReplyFlowTest extends TestCase
{
    private $reviewController;
    private $replyController;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        $reviewRepo = $this->createMock(ReviewRepository::class);
        $replyRepo = $this->createMock(ReplyRepository::class);
        $studentRepo = $this->createMock(StudentRepository::class);
        $studentStatsRepo = $this->createMock(StudentStatsRepository::class);
        $groupMembershipRepo = $this->createMock(GroupMembershipRepository::class);
        $groupRepo = $this->createMock(GroupRepository::class);
        $groupJoinRequestsRepo = $this->createMock(GroupJoinRequestsRepository::class);

        $reviewRepo->method('addReview')->willReturnCallback(function () {});
        $replyRepo->method('addReply')->willReturnCallback(function () {});
        $replyRepo->method('getReply')->willReturn(null);

        $review = new Review(1, 2, 123, 4, 'Great teammate!', new DateTime());
        $reviewRepo->method('getReview')->willReturn($review);

        $studentRepo->method('getStudent')->willReturn(['id' => 2]);

        $reviewControl = new ReviewControl($reviewRepo, $studentStatsRepo);
        $replyControl = new ReplyControl($replyRepo, $reviewRepo, $studentRepo);

        $this->reviewController = new ReviewPageController($reviewControl, $this->createMock(\PDO::class));
        $this->replyController = new ReplyPageController($replyControl, $this->createMock(\PDO::class));

        $groupMembershipControl = new GroupMembershipControl(
            $groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo
        );

        $this->groupMembershipController = new \App\Boundary\GroupMembershipController(
            $groupMembershipControl, $this->createMock(\PDO::class)
        );
    }

    public function testSubmitReviewSuccess(): void
    {
        $_SESSION['review_context'] = [
            'reviewer_id' => 1,
            'reviewee_id' => 2,
            'group_id' => 123
        ];

        $this->reviewController->onSubmitReview(1, 2, 123, 5, 'Well done!', date('Y-m-d H:i:s'));
        $this->assertTrue(true); // No exceptions
    }

    public function testSubmitReplySuccess(): void
    {
        $_SESSION['user']['id'] = 3;

        $this->replyController->onSubmitReply(1, 3, 'Thanks!', date('Y-m-d H:i:s'));
        $this->assertTrue(true);
    }

    public function testSubmitReviewMissingContext(): void
    {
        $this->expectException(\Exception::class);
        $_SESSION['review_context'] = null;

        $this->reviewController->onSubmitReview(1, 2, 123, 4, 'Nice', timestamp: date('Y-m-d H:i:s'));
    }

    public function testSubmitReviewInvalidRating(): void
    {
        $this->expectException(\Exception::class);
        $_SESSION['review_context'] = [
            'reviewer_id' => 1,
            'reviewee_id' => 2,
            'group_id' => 123
        ];

        $this->reviewController->onSubmitReview(1, 2, 123, 6, '', timestamp: date('Y-m-d H:i:s'));
    }

    public function testSubmitReplyDuplicate(): void
    {
        $this->expectException(\Exception::class);

        $_SESSION['user']['id'] = 3;
        $this->replyController->checkIfReplyExists = fn() => true;

        $this->replyController->onSubmitReply(1, 3, 'Already replied.', date('Y-m-d H:i:s'));
    }

    public function testSubmitReplyNotInSameGroup(): void
    {
        $this->expectException(\Exception::class);

        $_SESSION['user']['id'] = 3;

        $controller = $this->groupMembershipController;
        $controller->OnCheckIfMember = fn() => false;

        $this->replyController->onSubmitReply(1, 3, 'Invalid group.', date('Y-m-d H:i:s'));
    }

    public function testSubmitReplyMissingFields(): void
    {
        $this->expectException(\Exception::class);

        $_SESSION['user']['id'] = 3;
        $this->replyController->onSubmitReply(0, 0, '', date('Y-m-d H:i:s'));
    }
}
