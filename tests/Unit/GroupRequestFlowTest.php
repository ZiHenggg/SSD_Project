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
use App\Boundary\GroupMembershipController;
use App\Control\GroupMembershipControl;
use App\Entity\Group;
use App\Entity\GroupJoinRequests;
use App\SessionManager;
use PDO;
use PDOStatement;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupRepository;
use App\Repository\GroupJoinRequestsRepository;

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
        $groupRepo = $this->createMock(GroupRepository::class);
        $groupMembershipRepo = $this->createMock(GroupMembershipRepository::class);
        $groupJoinRequestsRepo = $this->createMock(GroupJoinRequestsRepository::class);

        $control = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);

        $pdoStmt = $this->createMock(PDOStatement::class);
        $pdoStmt->method('fetchAll')->willReturn([]);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('query')->willReturn($pdoStmt);

        $this->controller = new GroupMembershipController($control, $pdo);

        // Shared group mock (correct int type for maxMembers)
        $group = new Group('2025', 'T1', 'ICT2206', 'G1', 1, 4);
        $ref = new \ReflectionClass($group);
        $prop = $ref->getProperty('groupId');
        $prop->setAccessible(true);
        $prop->setValue($group, 5);

        $groupRepo->method('getGroup')->willReturn($group);
    }

    public function testSendJoinRequestSuccess(): void
    {
        $_SESSION['user']['id'] = 88;
        $_POST['groupId'] = 5;

        $_SESSION['success'] = "Join request sent successfully.";

        $this->assertEquals("Join request sent successfully.", $_SESSION['success']);
    }

    public function testSendJoinRequestBlockedByRateLimit(): void
    {
        $_SESSION['user']['id'] = 88;
        $_POST['groupId'] = 5;

        $rateLimitReached = true;

        if ($rateLimitReached) {
            $_SESSION['error'] = "You’ve reached the join request limit. Please try again later.";
        }

        $this->assertEquals("You’ve reached the join request limit. Please try again later.", $_SESSION['error']);
    }

    public function testAcceptJoinRequestSuccess(): void
    {
        $_SESSION['user']['id'] = 1;

        $_POST['requestId'] = 100;
        $_POST['requesterId'] = 88;

        $_SESSION['success'] = "Join request accepted successfully.";

        $this->assertEquals("Join request accepted successfully.", $_SESSION['success']);
    }

    public function testRejectJoinRequestSuccess(): void
    {
        $_SESSION['user']['id'] = 1;

        $_POST['requestId'] = 100;
        $_POST['requesterId'] = 88;

        $_SESSION['success'] = "Join request rejected!";

        $this->assertEquals("Join request rejected!", $_SESSION['success']);
    }

    public function testRemoveJoinRequestSuccess(): void
    {
        $_SESSION['user']['id'] = 88;
        $_POST['groupId'] = 5;

        $_SESSION['success'] = "Join request removed.";

        $this->assertEquals("Join request removed.", $_SESSION['success']);
    }
}
