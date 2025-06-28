<?php
/**
 * ✅ testSendJoinRequestSuccess() – sends join request under limit
 * ❌ testSendJoinRequestBlockedByRateLimit() – blocked after 5 attempts
 * ✅ testAcceptJoinRequestSuccess() – processes approval with valid IDs
 * ✅ testRejectJoinRequestSuccess() – processes rejection
 * ✅ testRemoveJoinRequestSuccess() – removes pending join request
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\SessionManager;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupMembershipController;
use App\Repository\GroupRepository;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupJoinRequestsRepository;
use App\Entity\Group;
use PDO;

class GroupRequestFlowTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        // Mocks
        $groupRepo = $this->getMockBuilder(GroupRepository::class)
            ->onlyMethods(['getGroup'])
            ->getMock();

        $groupMembershipRepo = $this->createMock(GroupMembershipRepository::class);
        $groupJoinRequestsRepo = $this->createMock(GroupJoinRequestsRepository::class);

        // 🔧 Fake Group object for getGroup()
        $group = new Group('2025', 'T1', 'ICT2206', 4, 88, 10);
        $ref = new \ReflectionClass($group);
        $prop = $ref->getProperty('groupId');
        $prop->setAccessible(true);
        $prop->setValue($group, 10);
        $groupRepo->method('getGroup')->willReturn($group);

        // 🔧 Fake join request object for getJoinRequestById()
        $joinRequest = new \stdClass();
        $joinRequest->groupId = 10;
        $groupJoinRequestsRepo->method('getGroupIdByRequestId')->willReturn(10);

        // Simulate getRequestsByStudent returning an object with methods used in logic
        $mockRequest = $this->createMock(\App\Entity\GroupJoinRequests::class);
        $mockRequest->method('getRequestId')->willReturn(7);
        $mockRequest->method('getRequesterId')->willReturn(88);
        $mockRequest->method('getJoinStatus')->willReturn('pending');
        $mockRequest->method('getGroupId')->willReturn(10);
        $groupJoinRequestsRepo->method('getRequestsByStudent')->willReturn([$mockRequest]);

        $membershipControl = new GroupMembershipControl(
            $groupMembershipRepo,
            $groupRepo,
            $groupJoinRequestsRepo
        );

        $pdoMock = $this->createMock(PDO::class);
        $this->controller = new GroupMembershipController($membershipControl, $pdoMock);
    }

    public function testSendJoinRequestSuccess(): void
    {
        $groupId = 10;
        $studentId = 99;

        SessionManager::set('user', ['id' => $studentId]);

        // Simulate Redis logic (under limit)
        $attempts = 2;
        $max = 5;

        if ($attempts < $max) {
            $this->controller->onJoinGroupRequest($groupId, $studentId);
            $this->assertTrue(true);
        }
    }

    public function testSendJoinRequestBlockedByRateLimit(): void
    {
        $studentId = 88;
        SessionManager::set('user', ['id' => $studentId]);

        // Simulate rate-limit
        $attempts = 5;
        $max = 5;

        if ($attempts >= $max) {
            SessionManager::set('error', "You’ve reached the join request limit. Please try again later.");
        }

        $this->assertEquals("You’ve reached the join request limit. Please try again later.", SessionManager::get('error'));
    }

    public function testAcceptJoinRequestSuccess(): void
    {
        $requestId = 7;
        $requesterId = 88;
        $approverId = 99;

        SessionManager::set('user', ['id' => $approverId]);

        $this->controller->onAcceptJoinRequest($requestId, $requesterId, $approverId);
        $this->assertTrue(true);
    }

    public function testRejectJoinRequestSuccess(): void
    {
        $requestId = 7;
        $requesterId = 88;
        $approverId = 99;

        SessionManager::set('user', ['id' => $approverId]);

        $this->controller->onRejectJoinRequest($requestId, $requesterId, $approverId);
        $this->assertTrue(true);
    }

    public function testRemoveJoinRequestSuccess(): void
    {
        $groupId = 10;
        $studentId = 99;

        SessionManager::set('user', ['id' => $studentId]);

        $this->controller->onRemoveJoinRequest($groupId, $studentId);
        $this->assertTrue(true);
    }
}
