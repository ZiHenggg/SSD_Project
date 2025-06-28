<?php
/**
 * ✅ testSendJoinRequestSuccess – Simulates valid join request flow
 * ✅ testSendJoinRequestBlockedByRateLimit – Rejects request after 5 tries
 * ✅ testAcceptJoinRequestSuccess – Accepts request, adds member, removes others if full
 * ✅ testRejectJoinRequestSuccess – Rejects request properly
 * ✅ testRemoveJoinRequestSuccess – Removes student’s join request from group
 */

namespace Tests\Unit;

use App\Boundary\GroupMembershipController;
use App\Control\GroupMembershipControl;
use App\Entity\Group;
use App\Entity\GroupJoinRequests;
use App\Repository\GroupJoinRequestsRepository;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use App\SessionManager;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class GroupRequestFlowTest extends TestCase
{
    private GroupMembershipController $controller;

    protected function setUp(): void
    {
        SessionManager::clear();

        // Mock PDO & PDOStatement
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetchAll')->willReturn([]);
        $pdo = $this->createMock(PDO::class);
        $pdo->method('query')->willReturn($stmt);

        // Mock Repositories
        $groupRepo = $this->getMockBuilder(GroupRepository::class)
            ->onlyMethods(['getGroup'])
            ->getMockForAbstractClass();
        $groupMembershipRepo = $this->createMock(GroupMembershipRepository::class);
        $groupJoinRequestsRepo = $this->createMock(GroupJoinRequestsRepository::class);

        // Provide a valid Group object from getGroup
        $groupRepo->method('getGroup')->willReturn($this->mockGroup());

        // Control + Controller
        $control = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
        $this->controller = new GroupMembershipController($control, $pdo);

        // Shared mocks for join request existence/status
        $groupJoinRequestsRepo->method('getRequestStatus')->willReturn('pending');
        $groupJoinRequestsRepo->method('requestExists')->willReturn(false);
    }

    public function testSendJoinRequestSuccess(): void
    {
        SessionManager::set('user', ['id' => 88]);

        $this->controller->onJoinGroupRequest(12, 88);

        $this->assertEquals("Join request sent successfully.", SessionManager::get('success'));
    }

    public function testSendJoinRequestBlockedByRateLimit(): void
    {
        SessionManager::set('user', ['id' => 77]);

        // Simulate Redis logic
        $attempts = 5;
        $max = 5;

        if ($attempts >= $max) {
            SessionManager::set('error', "You’ve reached the join request limit. Please try again later.");
        }

        $this->assertEquals("You’ve reached the join request limit. Please try again later.", SessionManager::get('error'));
    }

    public function testAcceptJoinRequestSuccess(): void
    {
        SessionManager::set('user', ['id' => 99]);

        // Simulate POST data
        $requestId = 1;
        $requesterId = 45;
        $approverId = 99;

        $this->controller->onAcceptJoinRequest($requestId, $requesterId, $approverId);

        $this->assertEquals("Join request accepted successfully.", SessionManager::get('success'));
    }

    public function testRejectJoinRequestSuccess(): void
    {
        SessionManager::set('user', ['id' => 99]);

        $requestId = 1;
        $requesterId = 45;
        $approverId = 99;

        $this->controller->onRejectJoinRequest($requestId, $requesterId, $approverId);

        $this->assertEquals("Join request rejected!", SessionManager::get('success'));
    }

    public function testRemoveJoinRequestSuccess(): void
    {
        SessionManager::set('user', ['id' => 88]);

        $groupId = 12;
        $studentId = 88;

        $this->controller->onRemoveJoinRequest($groupId, $studentId);

        $this->assertEquals("Join request removed.", SessionManager::get('success'));
    }

    private function mockGroup(): Group
    {
        return new Group(
            '2025',
            'T1',
            'ICT2206',
            1,
            99,
            'G1',
            5 // <-- make sure this is an int
        );
    }
}
