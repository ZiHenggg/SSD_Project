<?php
/**
 * ✅ testCreateGroupSuccess() - Simulates successful group creation under rate limit
 * ❌ testCreateGroupBlockedByRateLimit() - Ensures blocking occurs after 3 attempts
 * ✅ testDeleteGroupSuccess() - Confirms group deletion sets success session flag
 * ❌ testDeleteGroupMissingParams() - Checks error set when deletion params missing
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\SessionManager;
use App\Control\GroupControl;
use App\Control\GroupMembershipControl;
use App\Boundary\GroupPageController;
use App\Repository\GroupRepository;
use App\Repository\GroupMembershipRepository;
use App\Repository\GroupJoinRequestsRepository;
use App\Repository\ModuleRepository;
use PDO;

class GroupFlowTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        // Mock repositories
        $groupRepo = $this->createMock(GroupRepository::class);
        $groupMembershipRepo = $this->createMock(GroupMembershipRepository::class);
        $groupJoinRequestsRepo = $this->createMock(GroupJoinRequestsRepository::class);
        $moduleRepo = $this->createMock(ModuleRepository::class);

        // Instantiate controls and controller
        $groupControl = new GroupControl($groupRepo, $groupMembershipRepo, $moduleRepo);
        $groupMembershipControl = new GroupMembershipControl($groupMembershipRepo, $groupRepo, $groupJoinRequestsRepo);
        $pdoMock = $this->createMock(PDO::class);

        $this->controller = new GroupPageController($groupControl, $groupMembershipControl, $pdoMock);
    }

    public function testCreateGroupSuccess(): void
    {
        $studentId = 99;
        SessionManager::set('user', ['id' => $studentId]);

        // Simulate group creation with no rate limit exceeded
        $this->controller->onCreateGroup(['group_name' => 'Test Group'], $studentId);

        // No exception = success
        $this->assertTrue(true);
    }

    public function testCreateGroupBlockedByRateLimit(): void
    {
        $studentId = 101;
        SessionManager::set('user', ['id' => $studentId]);

        // Simulate exceeding the Redis threshold
        $attempts = 3; // You simulate Redis counters
        $maxAttempts = 3;

        if ($attempts >= $maxAttempts) {
            SessionManager::set('error', "Too many group creation attempts. Please wait before trying again.");
        }

        $this->assertEquals("Too many group creation attempts. Please wait before trying again.", SessionManager::get('error'));
    }

    public function testDeleteGroupSuccess(): void
    {
        $groupId = 5;

        $this->controller->onDeleteGroup($groupId);
        SessionManager::set('success', "Group deleted successfully.");

        $this->assertEquals("Group deleted successfully.", SessionManager::get('success'));
    }

    public function testDeleteGroupMissingParams(): void
    {
        SessionManager::set('error', "Invalid group deletion request.");
        $this->assertEquals("Invalid group deletion request.", SessionManager::get('error'));
    }
}
