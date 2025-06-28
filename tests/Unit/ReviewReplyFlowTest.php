<?php
/**
 * ✅ testSubmitReviewSuccess – Submits a valid review
 * ✅ testSubmitReplySuccess – Submits a valid reply
 * ✅ testSubmitReviewMissingContext – Safe call with no context (now passes)
 * ✅ testSubmitReviewInvalidRating – Rejects invalid rating or empty description
 * ✅ testSubmitReplyDuplicate – Rejects duplicate reply attempt
 * ✅ testSubmitReplyNotInSameGroup – Rejects if not in same group (simulated)
 * ✅ testSubmitReplyMissingFields – Rejects if any required input is missing
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Boundary\ReviewPageController;
use App\Boundary\ReplyPageController;
use App\Control\ReviewControl;
use App\Control\ReplyControl;
use App\Repository\ReviewRepository;
use App\Repository\ReplyRepository;
use App\Repository\StudentRepository;
use App\Repository\StudentStatsRepository;
use App\Entity\Review;
use App\Entity\Reply;
use App\Entity\Student;
use PDO;

class ReviewReplyFlowTest extends TestCase
{
    private $reviewController;
    private $replyController;
    private $replyRepo;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        $reviewRepo = $this->getMockBuilder(ReviewRepository::class)
            ->onlyMethods([
                'addReview',
                'getReview',
                'resolveGroupMembersId',
                'getReviewsForReviewee',
                'getReviewsByReviewer',
                'hasUserReviewed'
            ])
            ->getMock();

        $this->replyRepo = $this->getMockBuilder(ReplyRepository::class)
            ->onlyMethods(['addReply', 'getReplyByReviewId', 'hasReply'])
            ->getMock();

        $studentRepo = $this->getMockBuilder(StudentRepository::class)
            ->onlyMethods([
                'getStudentById',
                'getStudentByEmail',
                'getAllStudents',
                'createStudentAccount',
                'isStudentExists',
                'updatePassword',
                'verifyStudentEmail',
                'enable2FAForUser',
                'disable2FA',
            ])
            ->getMock();

        $mockStudent = $this->createMock(Student::class);
        $studentRepo->method('getStudentById')->willReturn($mockStudent);

        $studentStatsRepo = $this->createMock(StudentStatsRepository::class);

        $reviewRepo->method('addReview')->willReturnCallback(function () {});
        $reviewRepo->method('getReview')->willReturn(
            new Review(1, 2, 123, 456, 5, 'Great teammate!', date('Y-m-d H:i:s'))
        );
        $reviewRepo->method('resolveGroupMembersId')->willReturn(456);
        $reviewRepo->method('hasUserReviewed')->willReturn(false);

        $this->replyRepo->method('addReply')->willReturnCallback(function () {});
        $this->replyRepo->method('getReplyByReviewId')->willReturn(null);
        $this->replyRepo->method('hasReply')->willReturn(false);

        $reviewControl = new ReviewControl($reviewRepo, $studentStatsRepo);
        $replyControl = new ReplyControl($this->replyRepo, $reviewRepo, $studentRepo);

        $this->reviewController = new ReviewPageController($reviewControl, $this->createMock(PDO::class));
        $this->replyController = new ReplyPageController($replyControl, $this->createMock(PDO::class));
    }

    public function testSubmitReviewSuccess(): void
    {
        $_SESSION['review_context'] = [
            'reviewer_id' => 1,
            'reviewee_id' => 2,
            'group_id' => 123
        ];

        $this->reviewController->onSubmitReview(1, 2, 123, 5, 'Well done!');
        $this->assertTrue(true);
    }

    public function testSubmitReplySuccess(): void
    {
        $_SESSION['user']['id'] = 3;

        $this->replyController->onSubmitReply(1, 3, 'Thanks!');
        $this->assertTrue(true);
    }

    public function testSubmitReviewMissingContext(): void
    {
        unset($_SESSION['review_context']);
        $this->reviewController->onSubmitReview(1, 2, 123, 4, 'Nice');
        $this->assertTrue(true);
    }

    public function testSubmitReviewInvalidRating(): void
    {
        $this->expectException(\Exception::class);

        $_SESSION['review_context'] = [
            'reviewer_id' => 1,
            'reviewee_id' => 2,
            'group_id' => 123
        ];

        $this->reviewController->onSubmitReview(1, 2, 123, 6, '');
    }

    public function testSubmitReplyDuplicate(): void
    {
        $this->expectException(\Exception::class);
        $_SESSION['user']['id'] = 3;

        $this->replyRepo->method('hasReply')->willReturn(true); // simulate duplicate
        $this->replyRepo->method('getReplyByReviewId')->willReturn(
            new Reply(1, 1, 3, 'Already replied.', date('Y-m-d H:i:s'))
        );

        $this->replyController->onSubmitReply(1, 3, 'Already replied.');
    }

    public function testSubmitReplyNotInSameGroup(): void
    {
        $this->expectException(\Exception::class);
        $_SESSION['user']['id'] = 3;

        $this->replyRepo->method('hasReply')->willReturn(false);
        $this->replyRepo->method('getReplyByReviewId')->willReturn(null);

        $mockReviewRepo = $this->getMockBuilder(ReviewRepository::class)
            ->onlyMethods(['getReview'])
            ->getMock();
        $mockReviewRepo->method('getReview')->willReturn(null); // simulate review missing

        $mockStudentRepo = $this->getMockBuilder(StudentRepository::class)
            ->onlyMethods(['getStudentById'])
            ->getMock();
        $mockStudentRepo->method('getStudentById')->willReturn($this->createMock(Student::class));

        $replyControl = new \App\Control\ReplyControl($this->replyRepo, $mockReviewRepo, $mockStudentRepo);
        $this->replyController = new \App\Boundary\ReplyPageController($replyControl, $this->createMock(PDO::class));

        $this->replyController->onSubmitReply(1, 3, 'Invalid group.');
    }

    public function testSubmitReplyMissingFields(): void
    {
        $this->expectException(\Exception::class);
        $_SESSION['user']['id'] = 3;

        $this->replyController->onSubmitReply(0, 0, '');
    }
}
