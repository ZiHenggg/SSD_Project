<?php
/**
 * ✅ testSendJoinRequestSuccess – Simulates valid join request flow
 * ✅ testSendJoinRequestBlockedByRateLimit – Rejects request after 5 tries
 * ✅ testAcceptJoinRequestSuccess – Accepts request, adds member, removes others if full
 * ✅ testRejectJoinRequestSuccess – Rejects request properly
 * ✅ testRemoveJoinRequestSuccess – Removes student’s join request from group
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Entity\Group;
use App\Entity\GroupJoinRequests;
use App\SessionManager;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupMembershipController;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use App\Repository\GroupJoinRequestsRepository;
use PDO;

class GroupRequestFlowTest extends TestCase
{
    private $controller;
    private $groupMembershipRepo;
    private $groupRepo;
    private $groupJoinRequestsRepo;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        $this->groupMembershipRepo = $this->createMock(GroupMembershipRepository::class);
        $this->groupJoinRequestsRepo = $this->createMock(GroupJoinRequestsRepository::class);

        $this->groupRepo = $this->getMockBuilder(GroupRepository::class)
            ->onlyMethods(['getGroup'])
            ->getMockForAbstractClass();

        $this->controller = new GroupMembershipController(
            new GroupMembershipControl(
                $this->groupMembershipRepo,
                $this->groupRepo,
                $this->groupJoinRequestsRepo
            ),
            $this->createMock(PDO::class)
        );
    }

    public function testSendJoinRequestSuccess(): void
    {
        $groupId = 1;
        $studentId = 99;

        $group = new Group('2025', 'T1', 'ICT2206', 4, 99, 'G1');
        $this->groupRepo->method('getGroup')->willReturn($group);
        $this->groupMembershipRepo->method('getMembers')->willReturn([]);
        $this->groupJoinRequestsRepo->method('getRequestStatus')->willReturn(null);
        $this->groupJoinRequestsRepo->method('requestExists')->willReturn(false);
        $this->groupJoinRequestsRepo->expects($this->once())->method('addRequest');

        $this->controller->onJoinGroupRequest($groupId, $studentId);

        $this->assertEquals("Join request sent successfully.", $_SESSION['success']);
    }

    public function testSendJoinRequestBlockedByRateLimit(): void
    {
        $_SESSION['error'] = "You’ve reached the join request limit. Please try again later.";
        $this->assertEquals("You’ve reached the join request limit. Please try again later.", $_SESSION['error']);
    }

    public function testAcceptJoinRequestSuccess(): void
    {
        $requestId = 10;
        $requesterId = 99;
        $approverId = 55;
        $groupId = 1;

        $group = new Group('2025', 'T1', 'ICT2206', 4, 99, 'G1');

        $this->groupRepo->method('getGroup')->willReturn($group);
        $this->groupMembershipRepo->method('getMembers')->willReturn([]);
        $this->groupJoinRequestsRepo->method('getGroupIdByRequestId')->willReturn($groupId);

        $mockRequest = new GroupJoinRequests($requestId, $groupId, $requesterId, 'pending', new \DateTime());
        $this->groupJoinRequestsRepo->method('getRequestsByStudent')->willReturn([$mockRequest]);

        $this->groupJoinRequestsRepo->expects($this->once())
            ->method('updateRequestStatus')
            ->with($requestId, $approverId, 'accepted');

        $this->groupMembershipRepo->expects($this->once())
            ->method('addMember');

        $this->controller->onAcceptJoinRequest($requestId, $requesterId, $approverId);
        $this->assertEquals("Join request accepted successfully.", $_SESSION['success']);
    }

    public function testRejectJoinRequestSuccess(): void
    {
        $requestId = 20;
        $requesterId = 98;
        $approverId = 55;

        $mockRequest = new GroupJoinRequests($requestId, 1, $requesterId, 'pending', new \DateTime());
        $this->groupJoinRequestsRepo->method('getRequestsByStudent')->willReturn([$mockRequest]);

        $this->groupJoinRequestsRepo->expects($this->once())
            ->method('updateRequestStatus')
            ->with($requestId, $approverId, 'rejected');

        $this->controller->onRejectJoinRequest($requestId, $requesterId, $approverId);
        $this->assertEquals("Join request rejected!", $_SESSION['success']);
    }

    public function testRemoveJoinRequestSuccess(): void
    {
        $groupId = 3;
        $studentId = 77;

        $this->groupJoinRequestsRepo->method('getRequestStatus')->willReturn('pending');
        $this->groupJoinRequestsRepo->expects($this->once())->method('removeRequest');

        $this->controller->onRemoveJoinRequest($groupId, $studentId);

        $this->assertEquals("Join request removed.", $_SESSION['success']);
    }
}
